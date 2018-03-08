<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssessmentRoleAlias implements ServiceLocatorAwareInterface
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

    // TODO there should not be duplicates in `ara` table, but there are
    public function saveAssessmentRoleAlias($alias)
    {
        $id = 0;
        if ($alias != '')
        {
            $data = array('role_alias' => $alias);
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }
        return $id;
    }

    public function getAssessmentRoleAlias($companyId, $arId)
    {
        $alias = 0;
        $ara_id = $this->getServiceLocator()->get('Assessment\Model\CompanyAssessmentRoleAlias')->getCompanyAssessmentRoleAliasId($companyId, $arId);
        $select = $this->tableGateway->getSql()->select();
        $select->where("ara_id = {$ara_id}");
        $resultSet = $this->tableGateway->selectWith($select)->buffer();
        foreach ($resultSet as $result) {
            $alias = ($result->role_alias);
        }
        return $alias;
    }

    public function getAliasIdByAliasString($aliasString)
    {
        $alias = 0;
        $select = $this->tableGateway->getSql()->select();
        $select->where("role_alias = '{$aliasString}'");
        $resultSet = $this->tableGateway->selectWith($select)->buffer();
        foreach ($resultSet as $result) {
            $alias = ($result->ara_id);
        }
        return $alias;
    }

    public function getAssessmentRoleAliases($companyId)
    {
        return $this->getServiceLocator()
                ->get('Assessment\Model\CompanyAssessmentRoleAlias')
                ->getCompanyAssessmentRoleAliases($companyId);
    }

}