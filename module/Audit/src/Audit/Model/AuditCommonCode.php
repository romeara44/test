<?php
namespace Audit\Model;

use Admin\Model\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class AuditCommonCode implements ServiceLocatorAwareInterface
{
    //protected $tableGateway;
    protected $serviceLocator;

    // public function __construct(TableGateway $tableGateway)
    // {
    //     $this->tableGateway = $tableGateway;
    // }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    
   public function getAuditRecordTable($auditRecordTable)
    {
        if (!$auditRecordTable) {
            $auditRecordTable = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable');
        }
        return $auditRecordTable;
    }


    
}