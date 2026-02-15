<?php

namespace Trainee;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements ConfigProviderInterface, ServiceProviderInterface, AutoloaderProviderInterface
{
    /**
     * Returns an array for passing to Zend\Loader\AutoloaderFactory.
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Returns configuration to merge with application configuration
     * 
     * @return array Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     * 
     * @return array|\Zend\ServiceManagerConfig
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/config/service.config.php';
    }

}
