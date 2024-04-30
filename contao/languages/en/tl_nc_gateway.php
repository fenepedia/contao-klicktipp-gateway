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

$GLOBALS['TL_LANG']['tl_nc_gateway']['type'][KlickTippGateway::NAME] = 'Klick-Tipp API';
$GLOBALS['TL_LANG']['tl_nc_gateway']['kt_api_username'] = ['Username', 'Username of the Klick-Tipp API connection credentials.'];
$GLOBALS['TL_LANG']['tl_nc_gateway']['kt_api_password'] = ['Password', 'Password of the Klick-Tipp API connection credentials.'];
