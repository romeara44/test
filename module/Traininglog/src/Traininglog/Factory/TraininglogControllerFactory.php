<?php
// Filename: /module/Traininglog/src/Traininglog/Factory/TraininglogControllerFactory.php
namespace Traininglog\Factory;

use Traininglog\Controller\TraininglogController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TraininglogControllerFactory implements FactoryInterface
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
        $tovutiService        = $realServiceLocator->get('Traininglog\Service\TovutiServiceInterface');

        return new TraininglogController($tovutiService);
    }
}