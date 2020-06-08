<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_action'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['tl_class' => 'clr', 'chosen' => true, 'mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
    'options' => [
        'subscriber_update',
    ],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_email'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 256, 'tl_class' => 'w50'],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 256, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_parameters'] = [
    'exclude' => true,
    'inputType' => 'keyValueWizard',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_nc_message']['palettes']['__selector__'][] = 'kt_action';
$GLOBALS['TL_DCA']['tl_nc_message']['palettes']['klicktipp'] = '{title_legend},title,gateway;{klicktipp_legend},kt_action,kt_parameters';
$GLOBALS['TL_DCA']['tl_nc_message']['subpalettes']['kt_action_subscriber_update'] = 'kt_email';
