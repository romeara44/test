<?php
 // Filename: /module/Client/src/Client/Factory/OrganizationControllerFactory.php
 namespace Client\Factory;

 use Client\Controller\OrganizationController;
 //use Client\Model\OrganizationRepositoryInterface;
 //use Interop\Container\ContainerInterface;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;



 class OrganizationControllerFactory implements FactoryInterface
 {
    
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return OrganizationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator             = $serviceLocator->getServiceLocator();
        $identityService                = $realServiceLocator->get('SanAuth\Service\IdentityServiceInterface');

        return new OrganizationController($identityService);
    }
 }

 ?>
