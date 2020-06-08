<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

use Fenepedia\ContaoKlickTippGateway\Gateway\KlickTippGateway;

$GLOBALS['NOTIFICATION_CENTER']['GATEWAY']['klicktipp'] = KlickTippGateway::class;
