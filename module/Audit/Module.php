<?php
namespace Audit;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getConfig()
    {
        $test = __DIR__ . '/config/module.config.php';
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
                      
                'Audit\Model\AuditInquiryTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditInquiryTableGateway');
                    $table = new \Audit\Model\AuditInquiryTable($tableGateway);
                    return $table;
                },
                'AuditInquiryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditInquiry());
                    return new TableGateway('audit_inquiry', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditPerformanceCriteriaTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditPerformanceCriteria());
                    return new TableGateway('audit_performance_criteria', $dbAdapter, null, $resultSetPrototype);
                },
                'AuditPerformanceCriteriaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditPerformanceCriteria());
                    return new TableGateway('audit_performance_criteria', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRecordTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordTableGateway');
                    $table = new \Audit\Model\AuditRecordTable($tableGateway);
                    return $table;
                },
                'AuditRecordTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecord());
                    return new TableGateway('audit_record', $dbAdapter, null, $resultSetPrototype);
                },
                
                'Audit\Model\AuditRecordItemTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordItemTableGateway');
                    $table = new \Audit\Model\AuditRecordItemTable($tableGateway);
                    return $table;
                },
                'AuditRecordItemTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecordItem());
                    return new TableGateway('audit_record_item', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRecordTypeTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordTypeTableGateway');
                    $table = new \Audit\Model\AuditRecordTypeTable($tableGateway);
                    return $table;
                },
                'AuditRecordTypeTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecordType());
                    return new TableGateway('audit_record_type', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRecordSectionTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordSectionTableGateway');
                    $table = new \Audit\Model\AuditRecordSectionTable($tableGateway);
                    return $table;
                },
                'AuditRecordSectionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecordSection());
                    return new TableGateway('audit_record_section', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRecordSectionMasterTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordSectionMasterTableGateway');
                    $table = new \Audit\Model\AuditRecordSectionMasterTable($tableGateway);
                    return $table;
                },
                'AuditRecordSectionMasterTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecordSectionMaster());
                    return new TableGateway('audit_record_section_master', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRecordItemMasterTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRecordItemMasterTableGateway');
                    $table = new \Audit\Model\AuditRecordItemMasterTable($tableGateway);
                    return $table;
                },
                'AuditRecordItemMasterTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRecordItemMaster());
                    return new TableGateway('audit_record_item_master', $dbAdapter, null, $resultSetPrototype);
                },

                'Audit\Model\AuditRoleLocationContactTable' =>  function($sm) {
                    $tableGateway = $sm->get('AuditRoleLocationContactTableGateway');
                    $table = new \Audit\Model\AuditRoleLocationContactTable($tableGateway);
                    return $table;
                },
                'AuditRoleLocationContactTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Audit\Model\AuditRoleLocationContact());
                    return new TableGateway('audit_role_location_contact', $dbAdapter, null, $resultSetPrototype);
                },
                'Audit\Model\AuditCommonCode' =>  function($sm) {
                    $table = new \Audit\Model\AuditCommonCode();
                    
                    return $table;
                },
                

            ),
        );
    }
}
