<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CompanyAssessmentRoleAlias implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getCompanyAssessmentRoleAlias($companyId, $arId)
    {
        // TODO implement this

    }

    public function getCompanyAssessmentRoleAliases($companyId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where("c_id = " . $companyId);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }
}