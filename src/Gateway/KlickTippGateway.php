<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoKlickTippGateway\Gateway;

use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Fenepedia\ContaoKlickTippGateway\Exception\KlickTippGatewayException;
use Kazin8\KlickTipp\Connector;
use NotificationCenter\Gateway\GatewayInterface;
use NotificationCenter\Model\Message;

class KlickTippGateway extends \NotificationCenter\Gateway\Base implements GatewayInterface
{
    public const KT_API_URL = 'https://api.klick-tipp.com';

    /**
     * @var Connector
     */
    private $ktConnector;

    public function send(Message $message, array $tokens, $language = '')
    {
        try {
            switch ($message->kt_action) {
                case 'subscribe': return $this->subscribe($message, $tokens); break;
                case 'subscriber_update': return $this->subscriberUpdate($message, $tokens); break;
                case 'tag': return $this->tag($message, $tokens); break;
                default: throw new KlickTippGatewayException('Action "'.$message->kt_action.'" is currently not implemented.');
            }
        } catch (KlickTippGatewayException $e) {
            /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
            $kernel = System::getContainer()->get('kernel');
            if ($kernel->isDebug()) {
                throw $e;
            }
            System::log($e->getMessage(), __METHOD__, TL_ERROR);

            return false;
        }
    }

    protected function subscribe(Message $message, array $tokens): bool
    {
        $email = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_email, $tokens);

        if (empty($email) || !Validator::isEmail($email)) {
            throw new KlickTippGatewayException('Invalid email address given.');
        }

        $kt = $this->getConnector();

        $subscriberId = $kt->subscriber_search($email);
        $this->checkError($kt);

        if (!empty($subscriberId)) {
            System::log('Klick-Tipp subscriber with email "'.$email.'" already existent', __METHOD__, TL_GENERAL);

            return true;
        }

        $listId = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_list_id, $tokens) ?: 0;
        $tagId = $this->getTagId($message, $tokens) ?: 0;
        $fields = $this->getParameters($message, $tokens);

        $kt->subscriber_update($email, $listId, $tagId, $fields);
        $this->checkError($kt);

        return true;
    }

    protected function subscriberUpdate(Message $message, array $tokens): bool
    {
        $email = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_email, $tokens);

        if (empty($email) || !Validator::isEmail($email)) {
            throw new KlickTippGatewayException('Invalid email address given.');
        }

        $kt = $this->getConnector();

        $subscriberId = $kt->subscriber_search($email);
        $this->checkError($kt);

        if (empty($subscriberId)) {
            System::log('Could not find Klick-Tipp subscriber with email "'.$email.'"', __METHOD__, TL_GENERAL);

            return true;
        }

        $params = $this->getParameters($message, $tokens);

        System::log('Updating Klick-Tipp subscriber "'.$subscriberId.'" ('.$email.') with '.json_encode($params), __METHOD__, TL_GENERAL);
        $kt->subscriber_update($subscriberId, $params);
        $this->checkError($kt);

        return true;
    }

    protected function tag(Message $message, array $tokens): bool
    {
        $email = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_email, $tokens);

        if (empty($email) || !Validator::isEmail($email)) {
            throw new KlickTippGatewayException('Invalid email address given.');
        }

        $tagId = $this->getTagId($message, $tokens);

        if (empty($tagId)) {
            throw new KlickTippGatewayException('No tag given.');
        }

        System::log('Tagging Klick-Tipp subscriber "'.$email.'" with tag ID "'.$tagId.'"', __METHOD__, TL_GENERAL);
        $kt = $this->getConnector();
        $kt->tag($email, $tagId);
        $this->checkError($kt);

        return true;
    }

    protected function getConnector(): Connector
    {
        if (null !== $this->ktConnector) {
            return $this->ktConnector;
        }

        $kt = new Connector(self::KT_API_URL);
        $this->checkError($kt);

        $gateway = $this->getModel();
        $kt->login($gateway->kt_api_username, $gateway->kt_api_password);
        $this->checkError($kt);

        $this->ktConnector = $kt;

        return $this->ktConnector;
    }

    protected function checkError(Connector $kt): void
    {
        if (!empty($error = $kt->get_last_error())) {
            throw new KlickTippGatewayException(Connector::class.': '.$error);
        }
    }

    private function getTagId(Message $message, array $tokens): ?string
    {
        $tag = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_tag, $tokens);

        if (empty($tag)) {
            return null;
        }

        if (is_numeric($tag)) {
            return $tag;
        }

        $kt = $this->getConnector();

        /** @var array $tags */
        $tags = $kt->tag_index();
        $this->checkError($kt);

        if (empty($tags)) {
            throw new KlickTippGatewayException('No tags defined.');
        }

        $tagId = array_search($tag, $tags, true);

        if (false === $tagId) {
            throw new KlickTippGatewayException('Tag "'.$tag.'" not found.');
        }

        return $tagId;
    }

    private function getParameters(Message $message, array $tokens): ?array
    {
        $messageParams = StringUtil::deserialize($message->kt_parameters, true);
        $processedParams = [];

        foreach ($messageParams as $param) {
            $key = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($param['key'], $tokens);
            $value = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($param['value'], $tokens);

            // Do some type casting
            if (is_numeric($value)) {
                $value = (float) $value;
            }

            $processedParams[$key] = $value;
        }

        return $processedParams;
    }
}
