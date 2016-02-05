<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'itassetinventory\Controller\itassetinventory' => 'Itassetinventory\Controller\ItassetinventoryController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'itassetinventory' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/itassetinventory[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'search' => '.*',
                        'roleFilter' => '[0-9_]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Itassetinventory\Controller\ItAssetInventory',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'itassetinventory' => __DIR__ . '/../view',
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);