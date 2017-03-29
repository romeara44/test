<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/c1/:c1][/c2/:c2][/c3/:c3][/c4/:c4][/st/:st]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'c1' => '[0-9]+',
                        'c2' => '[0-9]+',
                        'c3' => '[0-9]+',
                        'c4' => '[0-9]+',
                        'st' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
            ),


        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'practice' => __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'renderPhoto' => 'Admin\View\Helper\RenderPhoto',
        )
    )
);