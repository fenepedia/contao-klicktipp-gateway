<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoKlickTippGateway\Parcel\Stamp;

use Fenepedia\ContaoKlickTippGateway\Config\KlickTippConfig;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\AbstractConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class KlickTippConfigStamp extends AbstractConfigStamp
{
    public function __construct(public KlickTippConfig $klickTippConfig)
    {
        parent::__construct($this->klickTippConfig);
    }

    public static function fromArray(array $data): StampInterface
    {
        return new self(KlickTippConfig::fromArray($data));
    }
}
