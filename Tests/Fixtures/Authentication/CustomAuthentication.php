<?php
/*
 * This file is a part of the CKFinder bundle for Symfony.
 *
 * Copyright (c) 2022, CKSource Holding sp. z o.o. All rights reserved.
 *
 * Licensed under the terms of the MIT license.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace CKSource\Bundle\CKFinderBundle\Tests\Fixtures\Authentication;

use CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom authentication fixture.
 */
class CustomAuthentication implements AuthenticationInterface
{

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;


    protected bool $authenticated = false;

    /**
     * @param bool $authenticated
     */
    public function setAuthenticated(bool $authenticated): void
    {
        $this->authenticated = $authenticated;
    }

    /**
     * @return bool
     */
    public function authenticate(): bool
    {
        return $this->authenticated;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        // stub
        $this->container = $container;
    }
}
