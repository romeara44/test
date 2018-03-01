<?php
namespace Assessment;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Assessment\Model\AssessmentTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentTableGateway');
                        $table = new \Assessment\Model\AssessmentTable($tableGateway);
                        return $table;
                },
                'AssessmentTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\Assessment());
                        return new TableGateway('assessments', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentRoleLocationContactTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentRoleLocationContactTableGateway');
                        $table = new \Assessment\Model\AssessmentRoleLocationContactTable($tableGateway);
                        return $table;
                    },
                'AssessmentRoleLocationContactTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentRoleLocationContact());
                        return new TableGateway('assessments_roles_locations_contacts', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentRoleTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentRoleTableGateway');
                        $table = new \Assessment\Model\AssessmentRoleTable($tableGateway);
                        return $table;
                },
                'Assessment\Model\AssessmentRoleAlias' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentRoleAliasGateway');
                    $table = new \Assessment\Model\AssessmentRoleAlias($tableGateway);
                    return $table;
                },
                'Assessment\Model\CompanyAssessmentRoleAlias' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyAssessmentRoleAliasGateway');
                    $table = new \Assessment\Model\CompanyAssessmentRoleAlias($tableGateway);
                    return $table;
                },
                'AssessmentRoleTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('assessments_roles', $dbAdapter, null, $resultSetPrototype);
                },
                'AssessmentRoleAliasGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('assessment_role_alias', $dbAdapter, null, $resultSetPrototype);
                },
                'CompanyAssessmentRoleAliasGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('company_assessment_role_alias', $dbAdapter, null, $resultSetPrototype);
                },
                'Assessment\Model\AssessmentInventoryTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentInventoryTableGateway');
                        $table = new \Assessment\Model\AssessmentInventoryTable($tableGateway);
                        return $table;
                },
                'AssessmentInventoryTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('assessments_inventory', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentInventoryLocationItemTable' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentInventoryLocationItemTableGateway');
                    $table = new \Assessment\Model\AssessmentInventoryLocationItemTable($tableGateway);
                    return $table;
                },
                'AssessmentInventoryLocationItemTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentInventoryLocationItem());
                    return new TableGateway('assessments_inventory_locations_items', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentInventoryLocationReportTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentInventoryLocationReportTableGateway');
                        $table = new \Assessment\Model\AssessmentInventoryLocationReportTable($tableGateway);
                        return $table;
                },
                'AssessmentInventoryLocationReportTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentInventoryLocationReport());
                        return new TableGateway('assessments_inventory_locations_reports', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentBusinessAssociateLocationTable' =>  function($sm) {
                        $tableGateway = $sm->get('AssessmentBusinessAssociateLocationTableGateway');
                        $table = new \Assessment\Model\AssessmentBusinessAssociateLocationTable($tableGateway);
                        return $table;
                },
                'AssessmentBusinessAssociateLocationTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentBusinessAssociateLocation());
                        return new TableGateway('assessments_business_associates_locations', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentQuestionTable' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentQuestionTableGateway');
                    $table = new \Assessment\Model\AssessmentQuestionTable($tableGateway);
                    return $table;
                },
                'AssessmentQuestionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentQuestion());
                    return new TableGateway('assessments_questions', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentQuestionOptionTable' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentQuestionOptionTableGateway');
                    $table = new \Assessment\Model\AssessmentQuestionOptionTable($tableGateway);
                    return $table;
                },
                'AssessmentQuestionOptionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentQuestionOption());
                    return new TableGateway('assessments_questions_options', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentQuestionAnswerTable' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentQuestionAnswerTableGateway');
                    $table = new \Assessment\Model\AssessmentQuestionAnswerTable($tableGateway);
                    return $table;
                },
                'AssessmentQuestionAnswerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentQuestionAnswer());
                    return new TableGateway('assessments_questions_answers', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\RemediationplanTable' =>  function($sm) {
                        $tableGateway = $sm->get('RemediationplanTableGateway');
                        $table = new \Assessment\Model\RemediationplanTable($tableGateway);
                        return $table;
                    },
                'RemediationplanTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\Remediationplan());
                        return new TableGateway('remediation_plans', $dbAdapter, null, $resultSetPrototype);
                    },

                'Assessment\Model\RemediationplanactionTable' =>  function($sm) {
                        $tableGateway = $sm->get('RemediationplanactionTableGateway');
                        $table = new \Assessment\Model\RemediationplanactionTable($tableGateway);
                        return $table;
                    },
                'RemediationplanactionTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\Remediationplanaction());
                        return new TableGateway('remediation_plans_actions', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\AssessmentQuestionCategoryTable' =>  function($sm) {
                    $tableGateway = $sm->get('AssessmentQuestionCategoryTableGateway');
                    $table = new \Assessment\Model\AssessmentQuestionCategoryTable($tableGateway);
                    return $table;
                },
                'AssessmentQuestionCategoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Assessment\Model\AssessmentQuestionCategory());
                    return new TableGateway('assessments_questions_categories', $dbAdapter, null, $resultSetPrototype);
                },

                'Assessment\Model\CorporateUserTable' =>  function($sm) {
                        $tableGateway = $sm->get('CorporateUserTableGateway');
                        $table = new \Assessment\Model\CorporateUserTable($tableGateway);
                        return $table;
                    },
                'CorporateUserTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('corporate_users', $dbAdapter, null, $resultSetPrototype);
                    },

                    
                'Client\Model\CompanyRolesTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyRolesTableGateway');
                    $table = new \Client\Model\CompanyRolesTable($tableGateway);
                    return $table;
                },
                'CompanyRolesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyRoles());
                    return new TableGateway('company_roles', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
