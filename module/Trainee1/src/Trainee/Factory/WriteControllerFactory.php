<?php
// Filename: /module/Trainee/src/Trainee/Factory/WriteControllerFactory.php
namespace Trainee\Factory;

use Trainee\Controller\WriteController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WriteControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $postService        = $realServiceLocator->get('Trainee\Service\PostServiceInterface');
        $postInsertForm     = $realServiceLocator->get('FormElementManager')->get('Trainee\Form\PostForm');

        return new WriteController(
            $postService,
            $postInsertForm
        );
    }
}