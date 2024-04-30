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
    'eval' => ['chosen' => true, 'mandatory' => true, 'tl_class' => 'clr w50', 'includeBlankOption' => true, 'submitOnChange' => true],
    'options' => [
        'subscribe',
        'subscriber_update',
        'tag',
    ],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_email'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_parameters'] = [
    'exclude' => true,
    'inputType' => 'keyValueWizard',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_tag'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr w50'],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['fields']['kt_list_id'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr w50'],
    'exclude' => true,
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_nc_message']['palettes']['__selector__'][] = 'kt_action';
$GLOBALS['TL_DCA']['tl_nc_message']['palettes']['klicktipp'] = '{title_legend},title,gateway;{klicktipp_legend},kt_action;{publish_legend},published';
$GLOBALS['TL_DCA']['tl_nc_message']['subpalettes']['kt_action_subscribe'] = 'kt_email,kt_list_id,kt_tag,kt_parameters';
$GLOBALS['TL_DCA']['tl_nc_message']['subpalettes']['kt_action_subscriber_update'] = 'kt_email,kt_parameters';
$GLOBALS['TL_DCA']['tl_nc_message']['subpalettes']['kt_action_tag'] = 'kt_email,kt_tag';
