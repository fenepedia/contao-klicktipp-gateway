<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['kt_api_username'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50', 'decodeEntities' => true, 'preserveTags' => true],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['kt_api_password'] = [
    'inputType' => 'password',
    'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50', 'decodeEntities' => true, 'preserveTags' => true],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_gateway']['palettes']['klicktipp'] = '{title_legend},title,type;{gateway_legend},kt_api_username,kt_api_password';
