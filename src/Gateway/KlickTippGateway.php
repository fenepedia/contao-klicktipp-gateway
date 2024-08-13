<?php

declare(strict_types=1);

namespace Fenepedia\ContaoKlickTippGateway\Gateway;

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\Validator;
use Fenepedia\ContaoKlickTippGateway\Config\KlickTippConfig;
use Fenepedia\ContaoKlickTippGateway\Exception\KlickTippGatewayException;
use Fenepedia\ContaoKlickTippGateway\Parcel\Stamp\KlickTippConfigStamp;
use Haste\Util\StringUtil as HasteStringUtil;
use Kazin8\KlickTipp\Connector;
use Psr\Log\LoggerInterface;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Gateway\GatewayInterface;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

class KlickTippGateway implements GatewayInterface
{
    public const NAME = 'klicktipp';

    public function __construct(
        private readonly ContaoFramework $contaoFramework,
        private readonly Connector $connector,
        private readonly LoggerInterface $contaoGeneralLogger,
        private readonly LoggerInterface $contaoErrorLogger,
        private readonly StringParser|null $stringParser = null,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function sealParcel(Parcel $parcel): Parcel
    {
        return $parcel
            ->withStamp($this->createKlickTippConfigStamp($parcel))
            ->seal()
        ;
    }

    public function sendParcel(Parcel $parcel): Receipt
    {
        try {
            $this->loginConnector($parcel);
            $config = $parcel->getStamp(KlickTippConfigStamp::class)->klickTippConfig;

            match ($action = $config->getAction()) {
                'subscribe' => $this->subscribe($config),
                'subscriber_update' => $this->subscriberUpdate($config),
                'tag' => $this->tag($config),
                default => throw new KlickTippGatewayException('Action "'.$action.'" is not implemented.'),
            };

            return Receipt::createForSuccessfulDelivery($parcel);
        } catch (\Throwable $e) {
            $this->contaoErrorLogger->error('Klick-Tipp API Request failed: '.$e->getMessage(), ['exception' => $e]);

            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfGatewayException(self::NAME, exception: $e),
            );
        }
    }

    private function createKlickTippConfigStamp(Parcel $parcel): KlickTippConfigStamp
    {
        $this->contaoFramework->initialize();

        $messageConfig = $parcel->getMessageConfig();
        $tokens = $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection->forSimpleTokenParser();
        $email = $this->recursiveReplaceTokensAndTags($messageConfig->getString('kt_email'), $tokens);

        if (!$email || !Validator::isEmail($email)) {
            throw new KlickTippGatewayException('Invalid email address "'.$email.'" given.');
        }

        $parameters = [];

        foreach (StringUtil::deserialize($messageConfig->getString('kt_parameters'), true) as $param) {
            $key = $this->recursiveReplaceTokensAndTags((string) $param['key'], $tokens);
            $value = $this->recursiveReplaceTokensAndTags((string) $param['value'], $tokens);

            // Do some type casting
            if (is_numeric($value)) {
                $value = (float) $value;
            }

            $parameters[$key] = $value;
        }

        return KlickTippConfigStamp::fromArray([
            'email' => $email,
            'action' => $messageConfig->getString('kt_action'),
            'list' => $messageConfig->getString('kt_list_id'),
            'tag' => $messageConfig->getString('kt_tag'),
            'parameters' => $parameters,
        ]);
    }

    private function subscribe(KlickTippConfig $config): void
    {
        $tagId = $this->getTagId($config->getTag()) ?: 0;

        $this->contaoGeneralLogger->info('Adding Klick-Tipp subscriber "'.$config->getEmail().'" (tag ID "'.$tagId.'", parameters: '.json_encode($config->getParameters(), JSON_THROW_ON_ERROR).').');
        $this->connector->subscribe($config->getEmail(), $config->getList(), $tagId, $config->getParameters());
        $this->checkError();
    }

    private function subscriberUpdate(KlickTippConfig $config): void
    {
        $email = $config->getEmail();

        $subscriberId = $this->connector->subscriber_search($email);
        $this->checkError();

        if (!$subscriberId) {
            $this->contaoGeneralLogger->info('Could not find Klick-Tipp subscriber with email "'.$email.'".');

            return;
        }

        $params = $config->getParameters();

        // Check if a new email address should be submitted (#5)
        $newemail = '';

        if (isset($params['email'])) {
            $newemail = $params['email'];
            unset($params['email']);
        }

        $this->contaoGeneralLogger->info('Updating Klick-Tipp subscriber "'.$subscriberId.'" ('.$email.') with '.json_encode($params, JSON_THROW_ON_ERROR));
        $this->connector->subscriber_update($subscriberId, $params, $newemail);
        $this->checkError();
    }

    private function tag(KlickTippConfig $config): void
    {
        $tagId = $this->getTagId($config->getTag());

        if (!$tagId) {
            throw new KlickTippGatewayException('No tag given.');
        }

        $email = $config->getEmail();

        $this->contaoGeneralLogger->info('Tagging Klick-Tipp subscriber "'.$email.'" with tag ID "'.$tagId.'".');
        $this->connector->tag($email, $tagId);
        $this->checkError();
    }

    private function loginConnector(Parcel $parcel): void
    {
        $this->checkError();

        $gatewayConfig = $parcel->getStamp(GatewayConfigStamp::class)->gatewayConfig;

        $this->connector->login($gatewayConfig->getString('kt_api_username'), $gatewayConfig->getString('kt_api_password'));

        $this->checkError();
    }

    private function checkError(): void
    {
        if ($error = $this->connector->get_last_error()) {
            throw new KlickTippGatewayException(Connector::class.': '.$error);
        }
    }

    /**
     * Returns the numeric KlickTipp ID for a given tag.
     */
    private function getTagId(string $tag): string|null
    {
        if (!$tag) {
            return null;
        }

        if (is_numeric($tag)) {
            return $tag;
        }

        /** @var array<string, string> $tags */
        $tags = $this->connector->tag_index();
        $this->checkError();

        if (!$tags) {
            throw new KlickTippGatewayException('No tags defined.');
        }

        $tagId = array_search($tag, $tags, true);

        if (false === $tagId) {
            throw new KlickTippGatewayException('Tag "'.$tag.'" not found.');
        }

        return $tagId;
    }

    /**
     * Compatibility layer to support both codefog/contao-haste v4 and v5.
     */
    private function recursiveReplaceTokensAndTags(string $text, array $tokens, int $textFlags = 0): string
    {
        if ($this->stringParser) {
            return $this->stringParser->recursiveReplaceTokensAndTags($text, $tokens, $textFlags);
        }

        if (class_exists(HasteStringUtil::class)) {
            return HasteStringUtil::recursiveReplaceTokensAndTags($text, $tokens, $textFlags);
        }

        throw new \RuntimeException('Cannot replace tokens and tags due to missing dependencies.');
    }
}
