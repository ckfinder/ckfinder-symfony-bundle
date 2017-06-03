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

namespace CKSource\Bundle\CKFinderBundle\Authentication;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Interface for the CKFinder authentication service.
 */
interface AuthenticationInterface extends ContainerAwareInterface
{
    /**
     * @return bool `true` if the current user was successfully authenticated within CKFinder.
     */
    public function authenticate();
}
