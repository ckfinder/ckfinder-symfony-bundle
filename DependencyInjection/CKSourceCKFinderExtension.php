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

namespace CKSource\Bundle\CKFinderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}.
 */
class CKSourceCKFinderExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $fileLocator =  new FileLocator(__DIR__.'/../Resources/config');

        $loader = new Loader\PhpFileLoader($container, $fileLocator);
        $loader->load('ckfinder_config.php');
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');

        $loader = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yml');
        $loader->load('form.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ckfinder.connector.class', $config['connector']['connectorClass']);
        $container->setParameter('ckfinder.connector.auth.class', $config['connector']['authenticationClass']);
        $container->setParameter('ckfinder.connector.config', $config['connector']);

        $this->registerFormThemes($container);
    }

    /**
     * Registers form resources for CKFinder form field types.
     *
     * @param ContainerBuilder $container
     */
    protected function registerFormThemes(ContainerBuilder $container)
    {
        $templatingEngines = $container->getParameter('templating.engines');

        if (in_array('twig', $templatingEngines)) {
            $container->setParameter(
                'twig.form.resources',
                array_merge(
                    array('CKSourceCKFinderBundle:Form:fields.html.twig'),
                    $container->getParameter('twig.form.resources')
                )
            );
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'ckfinder';
    }
}
