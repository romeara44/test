<?php
// Filename: /module/Trainee/src/Trainee/Factory/ListControllerFactory.php
namespace Trainee\Factory;

use Trainee\Controller\ListController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ListControllerFactory implements FactoryInterface
{
    /**
     * Create service
    *
    * @param ServiceLocatorInterface $serviceLocator
    *
    * @return mixed
    */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $postService        = $realServiceLocator->get('Trainee\Service\PostServiceInterface');

        return new ListController($postService);
    }
}