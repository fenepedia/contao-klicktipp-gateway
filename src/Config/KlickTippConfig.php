<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Klick-Tipp Gateway extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoKlickTippGateway\Config;

use Terminal42\NotificationCenterBundle\Config\AbstractConfig;

class KlickTippConfig extends AbstractConfig
{
    /**
     * Returns the configured API action.
     */
    public function getAction(): string
    {
        return $this->getString('action');
    }

    /**
     * Returns the configured target email.
     */
    public function getEmail(): string
    {
        return $this->getString('email');
    }

    /**
     * Returns the configured parameters for the API request.
     */
    public function getParameters(): array
    {
        return $this->get('parameters', []);
    }

    /**
     * Returns the configured KlickTipp tag for the API request.
     */
    public function getTag(): string
    {
        return $this->getString('tag');
    }

    /**
     * Returns the configured KlickTipp List ID for the API request.
     */
    public function getList(): string
    {
        return $this->getString('list');
    }
}
