<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Note\Controller\Files' => 'Note\Controller\FilesController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'files' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/files[/:action][/:type][/:note_id][/:file_id]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'note_id'     => '[0-9]+',
                        'file_id' => '[0-9]+',
                        'type' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Note\Controller\Files',
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'note' => __DIR__ . '/../view',
        ),
    ),

);