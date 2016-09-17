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

namespace CKSource\Bundle\CKFinderBundle;

use CKSource\Bundle\CKFinderBundle\DependencyInjection\CKSourceCKFinderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CKSourceCKFinderBundle
 */
class CKSourceCKFinderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new CKSourceCKFinderExtension();
    }
}
