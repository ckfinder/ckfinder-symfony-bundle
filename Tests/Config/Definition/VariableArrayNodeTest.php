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

namespace CKSource\Bundle\CKFinderBundle\Tests\Config\Definition;

use CKSource\Bundle\CKFinderBundle\Config\Definition\VariableArrayNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * ExtraValuesArrayNode test.
 */
class VariableArrayNodeTest extends TestCase
{
    /**
     * Test node if value is not an array
     */
    public function testNormalizeThrowsExceptionIfValueIsNotArray(): void
    {
        $node = new VariableArrayNode('root');
        $this->expectException(InvalidTypeException::class);
        $node->normalize('foo');
    }

    /**
     * Test merging
     */
    public function testMerge(): void
    {
        $builder = new TreeBuilder('root');
        $rootNode = $builder->getRootNode();

        $tree = $rootNode
                ->children()
                    ->setNodeClass('variableArray', 'CKSource\Bundle\CKFinderBundle\Config\Definition\Builder\VariableArrayNodeDefinition')
                    ->node('foo', 'scalar')->end()
                    ->node('bar', 'scalar')->end()
                    ->node('extra', 'variableArray')->end()
                ->end()
            ->end()
            ->buildTree();

        $a = [
            'foo' => 'bar',
            'extra' => [
                'foo' => 'a',
                'bar' => 'b'
            ]
        ];

        $b = [
            'foo' => 'moo',
            'bar' => 'b',
            'extra' => [
                'foo' => 'c',
                'baz' => 'd'
            ],
        ];

        $expected = [
            'foo' => 'moo',
            'bar' => 'b',
            'extra' => [
                'foo' => 'c',
                'bar' => 'b',
                'baz' => 'd'
            ]
        ];

        $this->assertEquals($expected, $tree->merge($a, $b));
    }

    /**
     * Test merging when used as a prototype
     */
    public function testMergeWhenUsedAsAPrototype(): void
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $builder = new TreeBuilder('root');
            $rootNode = $builder->getRootNode();
        } else {
            $builder = new TreeBuilder();
            $rootNode = $builder->root('root');
        }

        $tree = $rootNode
                ->children()
                    ->setNodeClass('variableArray', 'CKSource\Bundle\CKFinderBundle\Config\Definition\Builder\VariableArrayNodeDefinition')
                    ->node('foo', 'scalar')->end()
                    ->node('bar', 'scalar')->end()
                    ->arrayNode('backends')
                        ->useAttributeAsKey('name', false)
                        ->prototype('variableArray')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->buildTree();

        $a = [
            'foo' => 'bar',
            'backends' => [
                'cache' => [
                    'name' => 'cache',
                    'adapter' => 'local',
                    'root' => '/foo'
                ],
                'default' => [
                    'name' => 'default',
                    'adapter' => 'local',
                    'root' => '/bar'
                ]
            ]
        ];

        $b = [
            'foo' => 'moo',
            'backends' => [
                'cache' => [
                    'adapter' => 's3'
                ],
                'default' => [
                    'root' => '/bar/baz'
                ],
                'another' => [
                    'name' => 'another',
                    'adapter' => 's3'
                ]
            ],
        ];

        $expected = [
            'foo' => 'moo',
            'backends' => [
                'cache' => [
                    'name' => 'cache',
                    'adapter' => 's3',
                    'root' => '/foo'
                ],
                'default' => [
                    'name' => 'default',
                    'adapter' => 'local',
                    'root' => '/bar/baz'
                ],
                'another' => [
                    'name' => 'another',
                    'adapter' => 's3'
                ]
            ]
        ];

        $this->assertEquals($expected, $tree->merge($a, $b));
    }

    /**
     * Test node finalization
     */
    public function testFinalizeValue(): void
    {
        $node = new VariableArrayNode('foo', null);
        $this->assertSame(['a' => 'b'], $node->finalize(['a' => 'b']));
    }

    /**
     * Test node finalization without required keys present
     */
    public function testFinalizeValueWithoutRequiredKeys(): void
    {
        $node = new VariableArrayNode('foo', null, ['bar']);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The key "bar" at path "foo" must be configured.');
        $node->finalize(['a' => 'b']);
    }
}
