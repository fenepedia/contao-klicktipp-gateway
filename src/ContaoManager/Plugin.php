<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoKlickTippGateway\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Fenepedia\ContaoKlickTippGateway\ContaoKlickTippGatewayBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoKlickTippGatewayBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, 'notification_center']),
        ];
    }
}
