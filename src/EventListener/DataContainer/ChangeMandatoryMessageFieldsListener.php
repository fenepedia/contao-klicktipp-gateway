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
use Doctrine\DBAL\Connection;

/**
 * @Callback(table="tl_nc_message", target="config.onload")
 */
class ChangeMandatoryMessageFieldsListener
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        if (!$this->db->fetchOne("SELECT TRUE FROM tl_nc_message WHERE id = ? AND kt_action = 'tag'", [$dc->id])) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_tag']['eval']['mandatory'] = true;
    }
}
