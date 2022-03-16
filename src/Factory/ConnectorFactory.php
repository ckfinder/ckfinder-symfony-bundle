<?php

namespace CKSource\Bundle\CKFinderBundle\Factory;

use CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface;
use CKSource\Bundle\CKFinderBundle\Polyfill\CommandResolver;
use CKSource\CKFinder\CKFinder;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Kernel;

class ConnectorFactory
{
    /**
     * @var array
     */
    protected $connectorConfig;

    /**
     * @var AuthenticationInterface
     */
    protected $authenticationService;

    /**
     * @var CKFinder
     */
    protected $connectorInstance;

    /**
     * ConnectorFactory constructor.
     *
     * @param $connectorConfig
     * @param $authenticationService
     */
    public function __construct($connectorConfig, $authenticationService)
    {
        $this->connectorConfig = $connectorConfig;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return CKFinder
     */
    public function getConnector()
    {
        if ($this->connectorInstance) {
            return $this->connectorInstance;
        }

        $connector = new $this->connectorConfig['connectorClass']($this->connectorConfig);

        $connector['authentication'] = $this->authenticationService;

        if (Kernel::MAJOR_VERSION === 4) {
            $this->setupForV4Kernel($connector);
        }

        $this->connectorInstance = $connector;

        return $connector;
    }

    /**
     * Prepares the internal CKFinder's DI container to use the version 4+ of HttpKernel.
     *
     * @param \CKSource\CKFinder\CKFinder $ckfinder
     */
    protected function setupForV4Kernel($ckfinder)
    {
        $ckfinder['resolver'] = function () use ($ckfinder) {
            $commandResolver = new CommandResolver($ckfinder);
            $commandResolver->setCommandsNamespace(CKFinder::COMMANDS_NAMESPACE);
            $commandResolver->setPluginsNamespace(CKFinder::PLUGINS_NAMESPACE);
            return $commandResolver;
        };

        $ckfinder['kernel'] = function () use ($ckfinder) {
            return new HttpKernel(
                $ckfinder['dispatcher'],
                $ckfinder['resolver'],
                $ckfinder['request_stack'],
                $ckfinder['resolver']
            );
        };
    }
}
