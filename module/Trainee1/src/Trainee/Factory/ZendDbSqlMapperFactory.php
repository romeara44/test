<?php
 // Filename: /module/Trainee/src/Trainee/Factory/ZendDbSqlMapperFactory.php
 namespace Trainee\Factory;

 use Trainee\Mapper\ZendDbSqlMapper;
 //use Trainee\Model\Post;
 use Trainee\Entity\TraineeUser;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;
 use Zend\Stdlib\Hydrator\ClassMethods;

 class ZendDbSqlMapperFactory implements FactoryInterface
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
         return new ZendDbSqlMapper(
             $serviceLocator->get('Zend\Db\Adapter\Adapter'),
             new ClassMethods(false),
             new TraineeUser()
         );
     }
 }