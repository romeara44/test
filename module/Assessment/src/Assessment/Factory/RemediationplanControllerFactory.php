<?php
 // Filename: /module/Assessment/src/Assessment/Factory/RemediationplanControllerFactory.php
 
 namespace Assessment\Factory;

 use Assessment\Controller\RemediationplanController;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class RemediationplanControllerFactory implements FactoryInterface
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
            $realServiceLocator     = $serviceLocator->getServiceLocator();
            //$identityService        = $realServiceLocator->get('Assessment\Service\IdentityServiceInterface');
            //$initializeTableService = $realServiceLocator->get('Assessment\Service\InitializeTableServiceInterface');

         return new RemediationplanController();
     }
 }