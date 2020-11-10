<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoKlickTippGateway\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use NotificationCenter\Model\Message;

/**
 * @Callback(table="tl_nc_message", target="config.onload")
 */
class ChangeMandatoryMessageFieldsListener
{
    public function __invoke(DataContainer $dc): void
    {
        $message = Message::findById($dc->id);

        if (null === $message) {
            return;
        }

        if ('tag' === $message->kt_action) {
            $GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_tag']['eval']['mandatory'] = true;
        }
    }
}
