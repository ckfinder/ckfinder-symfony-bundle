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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more, see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ckfinder');
        $rootNode->append($this->addConnectorNode());

        return $treeBuilder;
    }

    /**
     * Creates the part of the configuration related to the CKFinder connector.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function addConnectorNode()
    {
        $treeBuilder = new TreeBuilder();
        $connectorNode = $treeBuilder->root('connector');

        $connectorNode
            ->children()
            ->setNodeClass('variableArray', 'CKSource\Bundle\CKFinderBundle\Config\Definition\Builder\VariableArrayNodeDefinition')
            ->scalarNode('connectorClass')->defaultValue('CKSource\CKFinder\CKFinder')->end()
            ->scalarNode('authenticationClass')->defaultValue('CKSource\Bundle\CKFinderBundle\Authentication\Authentication')->end()
            ->scalarNode('licenseName')->end()
            ->scalarNode('licenseKey')->end()
            ->arrayNode('privateDir')
                ->children()
                    ->scalarNode('backend')->defaultValue('default')->end()
                    ->variableNode('tags')->defaultValue('.ckfinder/tags')->end()
                    ->variableNode('logs')->defaultValue('.ckfinder/logs')->end()
                    ->variableNode('cache')->defaultValue('.ckfinder/cache')->end()
                    ->variableNode('thumbs')->defaultValue('.ckfinder/cache/thumbs')->end()
                ->end()
            ->end()
            ->arrayNode('images')
                ->children()
                    ->integerNode('maxWidth')->defaultValue(1600)->end()
                    ->integerNode('maxHeight')->defaultValue(1200)->end()
                    ->integerNode('quality')->defaultValue(80)->end()
                    ->arrayNode('sizes')
                        ->children()
                            ->arrayNode('small')
                                ->children()
                                    ->integerNode('width')->defaultValue(480)->end()
                                    ->integerNode('height')->defaultValue(320)->end()
                                    ->integerNode('quality')->defaultValue(80)->end()
                                ->end()
                            ->end()
                            ->arrayNode('medium')
                                ->children()
                                    ->integerNode('width')->defaultValue(600)->end()
                                    ->integerNode('height')->defaultValue(480)->end()
                                    ->integerNode('quality')->defaultValue(80)->end()
                                ->end()
                            ->end()
                            ->arrayNode('large')
                                ->children()
                                    ->integerNode('width')->defaultValue(800)->end()
                                    ->integerNode('height')->defaultValue(600)->end()
                                    ->integerNode('quality')->defaultValue(80)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('backends')
                ->useAttributeAsKey('name', false)
                ->prototype('variableArray')->requiresKeys(array('name', 'adapter'))->end()
            ->end()
            ->scalarNode('defaultResourceTypes')->end()
            ->arrayNode('resourceTypes')
                ->performNoDeepMerging()
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('backend')->isRequired()->end()
                        ->scalarNode('label')->end()
                        ->scalarNode('directory')->end()
                        ->scalarNode('allowedExtensions')->end()
                        ->scalarNode('deniedExtensions')->end()
                        ->scalarNode('maxSize')->end()
                        ->booleanNode('lazyLoad')->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('roleSessionVar')->end()
            ->arrayNode('accessControl')
                ->prototype('array')
                    ->children()
                        ->scalarNode('role')->isRequired()->end()
                        ->scalarNode('resourceType')->isRequired()->end()
                        ->scalarNode('folder')->isRequired()->end()
                        ->booleanNode('FOLDER_VIEW')->defaultTrue()->end()
                        ->booleanNode('FOLDER_CREATE')->defaultTrue()->end()
                        ->booleanNode('FOLDER_RENAME')->defaultTrue()->end()
                        ->booleanNode('FOLDER_DELETE')->defaultTrue()->end()
                        ->booleanNode('FILE_VIEW')->defaultTrue()->end()
                        ->booleanNode('FILE_UPLOAD')->defaultTrue()->end()
                        ->booleanNode('FILE_RENAME')->defaultTrue()->end()
                        ->booleanNode('FILE_DELETE')->defaultTrue()->end()
                        ->booleanNode('IMAGE_RESIZE')->defaultTrue()->end()
                        ->booleanNode('IMAGE_RESIZE_CUSTOM')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
            ->booleanNode('overwriteOnUpload')->defaultFalse()->end()
            ->booleanNode('checkDoubleExtension')->defaultTrue()->end()
            ->booleanNode('disallowUnsafeCharacters')->defaultFalse()->end()
            ->booleanNode('secureImageUploads')->defaultTrue()->end()
            ->booleanNode('checkSizeAfterScaling')->defaultTrue()->end()
            ->arrayNode('htmlExtensions')
                ->prototype('scalar')->end()
                ->defaultValue(array('html', 'htm', 'xml', 'js'))
            ->end()
            ->arrayNode('hideFolders')
                ->prototype('scalar')->end()
                ->defaultValue(array('.*', 'CVS', '__thumbs'))
            ->end()
            ->arrayNode('hideFiles')
                ->prototype('scalar')->end()
                ->defaultValue(array('.*'))
            ->end()
            ->booleanNode('forceAscii')->defaultFalse()->end()
            ->booleanNode('xSendfile')->defaultFalse()->end()
            ->booleanNode('debug')->defaultFalse()->end()
            ->arrayNode('debugLoggers')
                ->prototype('scalar')->end()
                ->defaultValue(array('ckfinder_log', 'error_log', 'firephp'))
            ->end()
            ->arrayNode('plugins')
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('cache')
                ->children()
                    ->integerNode('imagePreview')->defaultValue(24 * 3600)->end()
                    ->integerNode('thumbnails')->defaultValue(24 * 3600 * 365)->end()
                    ->integerNode('proxyCommand')->defaultValue(0)->end()
                ->end()
            ->end()
            ->scalarNode('tempDirectory')->end()
            ->booleanNode('sessionWriteClose')->defaultTrue()->end()
            ->booleanNode('csrfProtection')->defaultTrue()->end()
        ->end();

        return $connectorNode;
    }


}
