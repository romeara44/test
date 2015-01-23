<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Assessment\Controller\Assessment' => 'Assessment\Controller\AssessmentController',
            'Assessment\Controller\Remediationplan' => 'Assessment\Controller\RemediationplanController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'assessment' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/assessment[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/step/:step][/location/:location][/adrId/:adrId][/ailiId/:ailiId][/abalId/:abalId][/locationRole/:locationRole][/assessmentRole/:assessmentRole][/companyId/:companyId]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'step' => '[0-9]+',
                        'location' => '[0-9]+',
                        'adrId' => '[0-9]+',
                        'ailiId' => '[0-9]+',
                        'abalId' => '[0-9]+',
                        'locationRole' => '[0-9_]+',
                        'assessmentRole' => '[0-9_]+',
                        'companyId' => '[0-9_]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Assessment\Controller\Assessment',
                        'action'     => 'index',
                    ),
                ),
            ),

            'remediationplan' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/remediationplan[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/rpId/:rpId][/type/:type][/add_atts/:add_atts]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'rpId' => '[0-9]+',
                        'type' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'add_atts' => '[\,0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Assessment\Controller\Remediationplan',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'assessment' => __DIR__ . '/../view',
            'remediationplan' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'remediationplan/pdftemplate' => __DIR__ . '/../../../module/Assessment/view/assessment/remediationplan/edit.phtml',
        ),
    ),

);