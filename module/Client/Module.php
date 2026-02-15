<?php
namespace Client;

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
                'Client\Model\CompanyTable' =>  function($sm) {
                        $tableGateway = $sm->get('CompanyTableGateway');
                        $table = new \Client\Model\CompanyTable($tableGateway);
                        return $table;
                },
                'CompanyTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\Company());
                        return new TableGateway('companies', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\AddressTable' =>  function($sm) {
                        $tableGateway = $sm->get('AddressTableGateway');
                        $table = new \Client\Model\AddressTable($tableGateway);
                        return $table;
                },
                'AddressTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\Address());
                        return new TableGateway('addresses', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\AddressItemTable' =>  function($sm) {
                        $tableGateway = $sm->get('AddressItemTableGateway');
                        $table = new \Client\Model\AddressItemTable($tableGateway);
                        return $table;
                    },
                'AddressItemTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\AddressItem());
                        return new TableGateway('addresses_items', $dbAdapter, null, $resultSetPrototype);
                },

                'Note\Model\NoteTable' =>  function($sm) {
                    $tableGateway = $sm->get('NoteTableGateway');
                    $table = new \Note\Model\NoteTable($tableGateway);
                    return $table;
                },
                'NoteTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Note\Model\Note());
                    return new TableGateway('notes', $dbAdapter, null, $resultSetPrototype);
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

                'Client\Model\CompanyConsultantsTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyConsultantsTableGateway');
                    $table = new \Client\Model\CompanyConsultantsTable($tableGateway);
                    return $table;
                },
                'CompanyConsultantsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyConsultants());
                    return new TableGateway('company_consultants', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\CompanyTrainingManagersTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyTrainingManagersTableGateway');
                    $table = new \Client\Model\CompanyTrainingManagersTable($tableGateway);
                    return $table;
                },
                'CompanyTrainingManagersTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyTrainingManagers());
                    return new TableGateway('company_training_managers', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\CompanyTypesTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyTypesTableGateway');
                    $table = new \Client\Model\CompanyTypesTable($tableGateway);
                    return $table;
                },
                'CompanyTypesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyTypes());
                    return new TableGateway('company_type', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\CompanyModuleRoleTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyModuleRoleTableGateway');
                    $table = new \Client\Model\CompanyModuleRoleTable($tableGateway);
                    return $table;
                },
                'CompanyModuleRoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyModuleRole());
                    return new TableGateway('company_module_role', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\CompanyMasterRoleTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyMasterRoleTableGateway');
                    $table = new \Client\Model\CompanyMasterRoleTable($tableGateway);
                    return $table;
                },
                'CompanyMasterRoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyMasterRole());
                    return new TableGateway('company_master_role', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\CompanyModuleRoleAliasTable' =>  function($sm) {
                    $tableGateway = $sm->get('CompanyModuleRoleAliasTableGateway');
                    $table = new \Client\Model\CompanyModuleRoleAliasTable($tableGateway);
                    return $table;
                },
                'CompanyModuleRoleAliasTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\CompanyModuleRoleAlias());
                    return new TableGateway('company_module_role_alias', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\HipaaSuiteModuleRoleTable' =>  function($sm) {
                    $tableGateway = $sm->get('HipaaSuiteModuleRoleTableGateway');
                    $table = new \Client\Model\HipaaSuiteModuleRoleTable($tableGateway);
                    return $table;
                },
                'HipaaSuiteModuleRoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\HipaaSuiteModuleRole());
                    return new TableGateway('hipaa_suite_module_role', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\HipaaSuiteModuleTable' =>  function($sm) {
                    $tableGateway = $sm->get('HipaaSuiteModuleTableGateway');
                    $table = new \Client\Model\HipaaSuiteModuleTable($tableGateway);
                    return $table;
                },
                'HipaaSuiteModuleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\HipaaSuiteModule());
                    return new TableGateway('hipaa_suite_module', $dbAdapter, null, $resultSetPrototype);
                },

                'Client\Model\HipaaSuiteTypesTable' =>  function($sm) {
                    $tableGateway = $sm->get('HipaaSuiteTypesTableGateway');
                    $table = new \Client\Model\HipaaSuiteTypesTable($tableGateway);
                    return $table;
                },
                'HipaaSuiteTypesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Client\Model\HipaaSuiteTypes());
                    return new TableGateway('lu_hipaa_suite_type', $dbAdapter, null, $resultSetPrototype);
                },
                
            ),
        );
    }
}
