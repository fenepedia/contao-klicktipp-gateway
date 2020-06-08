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
    public function send(Message $message, array $tokens, $language = '')
    {
        try {
            if ('subscriber_update' !== $message->kt_action) {
                throw new KlickTippGatewayException('Only "subscriber_update" is currently implemented.');
            }

            $email = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($message->kt_email, $tokens);

            if (empty($email) || !Validator::isEmail($email)) {
                throw new KlickTippGatewayException('Invalid email address given.');
            }

            $kt = new Connector('https://api.klick-tipp.com');
            $this->checkError($kt);

            $gateway = $this->getModel();
            $kt->login($gateway->kt_api_username, $gateway->kt_api_password);
            $this->checkError($kt);

            $subscriberId = $kt->subscriber_search($email);
            $this->checkError($kt);

            if (empty($subscriberId)) {
                System::log('Could not find Klick-Tipp subscriber with email "'.$email.'"', __METHOD__, TL_GENERAL);
            }

            $messageParams = StringUtil::deserialize($message->kt_parameters);
            $processedParams = [];

            foreach ($messageParams as $param) {
                $key = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($param['key'], $tokens);
                $value = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($param['value'], $tokens);
                $processedParams[$key] = $value;
            }

            $kt->subscriber_update($subscriberId, $processedParams);
            $this->checkError($kt);

            return true;
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

    private function checkError(Connector $kt): void
    {
        if (!empty($error = $kt->get_last_error())) {
            throw new KlickTippGatewayException(Connector::class.': '.$error);
        }
    }
}
