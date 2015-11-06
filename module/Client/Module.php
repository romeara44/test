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
            ),
        );
    }
}
