<?php

namespace CKSource\Bundle\CKFinderBundle\Factory;

use CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface;
use CKSource\CKFinder\CKFinder;

class ConnectorFactory
{
    /**
     * @var array
     */
    protected array $connectorConfig;

    /**
     * @var AuthenticationInterface
     */
    protected AuthenticationInterface $authenticationService;

    /**
     * @var ?CKFinder
     */
    protected ?CKFinder $connectorInstance = null;

    /**
     * ConnectorFactory constructor.
     *
     * @param array $connectorConfig
     * @param AuthenticationInterface $authenticationService
     */
    public function __construct(array $connectorConfig, AuthenticationInterface $authenticationService)
    {
        $this->connectorConfig = $connectorConfig;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return CKFinder
     */
    public function getConnector(): CKFinder
    {
        if ($this->connectorInstance) {
            return $this->connectorInstance;
        }

        $connector = new $this->connectorConfig['connectorClass']($this->connectorConfig);

        $connector['authentication'] = $this->authenticationService;

        $this->connectorInstance = $connector;

        return $connector;
    }
}
