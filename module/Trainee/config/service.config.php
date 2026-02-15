<?php

namespace Trainee;

return array(
    // 'invokables' => array(
    //     'Trainee\Service\ILitmosGetService' => 'Trainee\Service\LitmosGetService',
    // ),

    'factories' => array(
        // 'Trainee\Repository\IPostRepository' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        //     $postRepository = new \Trainee\Repository\PostRepository();
        //     $postRepository->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));

        //     return $postRepository;
        // },

        // 'Trainee\Service\ILitmosGetService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        //     $litmosGetService = new \Trainee\Service\LitmosGetService();
        //     $litmosGetService->setPostRepository($serviceLocator->get('Trainee\Repository\IPostRepository'));

        //     return $litmosGetService;
        // },



        'Trainee\Repository\ITraineeRepository' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
            $postRepository = new \Trainee\Repository\TraineeRepository();
            $postRepository->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));

            return $postRepository;
        },

        'Trainee\Service\ILitmosGetService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
            $litmosGetService = new \Trainee\Service\LitmosGetService();
            $litmosGetService->setPostRepository($serviceLocator->get('Trainee\Repository\ITraineeRepository'));

            return $litmosGetService;
        },

        'Trainee\Service\ILitmosDeleteService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
            $litmosGetService = new \Trainee\Service\LitmosDeleteService();
            $litmosGetService->setPostRepository($serviceLocator->get('Trainee\Repository\ITraineeRepository'));

            return $litmosGetService;
        },

        'Trainee\Service\ILitmosPostService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
            $litmosGetService = new \Trainee\Service\LitmosPostService();
            $litmosGetService->setPostRepository($serviceLocator->get('Trainee\Repository\ITraineeRepository'));

            return $litmosGetService;
        },

        'Trainee\Service\ILitmosPutService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
            $litmosGetService = new \Trainee\Service\LitmosPutService();
            $litmosGetService->setPostRepository($serviceLocator->get('Trainee\Repository\ITraineeRepository'));

            return $litmosGetService;
        }
    )
);