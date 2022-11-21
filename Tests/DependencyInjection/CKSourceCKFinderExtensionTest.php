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

namespace CKSource\Bundle\CKFinderBundle\Tests\DependencyInjection;

use CKSource\Bundle\CKFinderBundle\DependencyInjection\CKSourceCKFinderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * CKSourceCKFinderExtension test.
 */
class CKSourceCKFinderExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected $extensionAlias;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $ckfinderExtension = new CKSourceCKFinderExtension();

        $this->extensionAlias = $ckfinderExtension->getAlias();

        $this->container = new ContainerBuilder();

        $this->container->setParameter('templating.engines', ['php', 'twig']);
        $this->container->setParameter('templating.helper.form.resources', []);
        $this->container->setParameter('twig.form.resources', []);
        $this->container->setParameter('kernel.cache_dir', '/var/cache');
        $this->container->setParameter('kernel.logs_dir', '/var/logs');
        $this->container->setParameter('kernel.project_dir', '');

        $this->container->registerExtension($ckfinderExtension);
        $this->container->loadFromExtension($ckfinderExtension->getAlias());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->container);
    }

    /**
     * Returns config fixture.
     *
     * @return array Connector config array fixture.
     */
    protected function getConfig(): array
    {
        return require __DIR__.'/../Fixtures/config/ckfinder_config.php';
    }

    /**
     * Tests default values set in the container.
     */
    public function testDefaultValues(): void
    {
        $this->container->compile();
        $this->assertSame('CKSource\CKFinder\CKFinder', $this->container->getParameter('ckfinder.connector.class'));
        $this->assertSame('CKSource\Bundle\CKFinderBundle\Authentication\Authentication', $this->container->getParameter('ckfinder.connector.auth.class'));
        $this->assertEquals($this->getConfig(), $this->container->getParameter('ckfinder.connector.config'));
    }

    /**
     * Tests default services.
     */
    public function testDefaultServices(): void
    {
        $this->container->compile();
        $this->assertInstanceOf('CKSource\CKFinder\CKFinder', $this->container->get('ckfinder.connector'));
        $this->assertInstanceOf('CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface', $this->container->get('ckfinder.connector.auth'));
    }

    /**
     * Tests overwriting backend options in the result config.
     */
    public function testOverwritingDefaultBackendsConfig(): void
    {
        $this->container->loadFromExtension($this->extensionAlias, [
            'connector' => [
                'backends' => [
                    'default' => [
                        'root' => '/foo/bar',
                        'baseUrl' => 'http://example.com/foo/bar'
                    ]
                ]
            ]
        ]);

        $this->container->compile();

        $config = $this->getConfig();
        $config['backends']['default']['root'] = '/foo/bar';
        $config['backends']['default']['baseUrl'] = 'http://example.com/foo/bar';

        $this->assertEquals($config, $this->container->getParameter('ckfinder.connector.config'));
    }

    /**
     * Tests appending new backends in the result config.
     */
    public function testAppendingDefaultBackendsConfig(): void
    {
        $newBackendConfig = [
            'name' => 'my_ftp',
            'adapter' => 'ftp',
            'root' => '/foo/bar',
            'baseUrl' => 'http://example.com/foo/bar',
            'host' => 'localhost',
            'username' => 'user',
            'password' => 'pass',
        ];

        $this->container->loadFromExtension($this->extensionAlias, [
            'connector' => [
                'backends' => [
                    'my_ftp' => $newBackendConfig
                ]
            ]
        ]);

        $this->container->compile();

        $config = $this->getConfig();

        $config['backends']['my_ftp'] = $newBackendConfig;

        $this->assertEquals($config, $this->container->getParameter('ckfinder.connector.config'));
    }

    /**
     * Tests the custom authentication service
     */
    public function testCustomAuthentication(): void
    {
        $authClass = 'CKSource\Bundle\CKFinderBundle\Tests\Fixtures\Authentication\CustomAuthentication';

        $this->container->loadFromExtension($this->extensionAlias, [
            'connector' => [
                'authenticationClass' => $authClass
            ]
        ]);

        $this->container->compile();

        /* @var $auth \CKSource\Bundle\CKFinderBundle\Tests\Fixtures\Authentication\CustomAuthentication */
        $auth = $this->container->get('ckfinder.connector.auth');
        $this->assertInstanceOf('CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface', $auth);
        $this->assertInstanceOf($authClass, $auth);

        /* @var $connector \CKSource\CKFinder\CKFinder */
        $connector = $this->container->get('ckfinder.connector');
        $connectorAuth = $connector->offsetGet('authentication');
        $this->assertSame($auth, $connectorAuth);

        $this->assertFalse($connectorAuth->authenticate());
        $auth->setAuthenticated(true);
        $this->assertTrue($connectorAuth->authenticate());
    }

    /**
     * Tests if the resourceType option is completely overwritten.
     */
    public function testIfResourceTypesAreNoDeeplyMerged(): void
    {
        $this->container->loadFromExtension($this->extensionAlias, [
            'connector' => [
                'resourceTypes' => [
                    'Custom' => [
                        'name' => 'Custom',
                        'backend' => 'default'
                    ]
                ]
            ]
        ]);

        $this->container->compile();

        $connectorConfig = $this->container->getParameter('ckfinder.connector.config');

        $expected = [
            'Custom' => [
                'name' => 'Custom',
                'backend' => 'default'
            ]
        ];

        $this->assertEquals($expected, $connectorConfig['resourceTypes']);

        $connector = $this->container->get('ckfinder.connector');

        $this->assertEquals(['Custom'], $connector['config']->getResourceTypes());
    }

    /**
     * Tests if the default resource types are used if the resourceType option is not set.
     */
    public function testIfDefaultResourceTypesAreSet(): void
    {
        $this->container->compile();

        $connector = $this->container->get('ckfinder.connector');

        $this->assertEquals(['Files', 'Images'], $connector['config']->getResourceTypes());
    }
}
