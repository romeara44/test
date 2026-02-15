<?php
// Filename: /module/Trainee/src/Trainee/Factory/DeleteControllerFactory.php
namespace Trainee\Factory;

use Trainee\Controller\DeleteController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DeleteControllerFactory implements FactoryInterface
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

        return new DeleteController($postService);
    }
}