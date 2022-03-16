<?php
/*
 * This file is a part of the CKFinder bundle for Symfony.
 *
 * Copyright (C) 2016, CKSource - Frederico Knabben. All rights reserved.
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
    protected $authenticated = false;

    /**
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool) $authenticated;
    }

    /**
     * @return bool
     */
    public function authenticate()
    {
        return $this->authenticated;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // stub
    }
}