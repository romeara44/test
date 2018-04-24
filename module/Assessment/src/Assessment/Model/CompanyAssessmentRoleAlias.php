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

    public function getCompanyAssessmentRoleAliasId($companyId, $arId)
    {
        $id = 0;
        $select = $this->tableGateway->getSql()->select();
        $select->where("c_id = " . $companyId);
        $select->where("ar_id = " . $arId);
        $resultSet = $this->tableGateway->selectWith($select);
        foreach ($resultSet as $result) {
            $id = $result->ara_id;
        }
        return $id;
    }

    public function getCompanyAssessmentRoleAliases($companyId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where("c_id = " . $companyId);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function deleteCompanyAssessmentRoleAlias($companyId, $arId)
    {
        $companyId = (int) $companyId;
        $arId = (int) $arId;
        $delete = $this->tableGateway->getSql()->delete();
        $delete->where(array("c_id = {$companyId}", "ar_id = {$arId}"));
        $resultSet = $this->tableGateway->deleteWith($delete);
        return $resultSet;
    }

    public function saveCompanyAssessmentRoleAlias($data)
    {
        $caraId = 0;

        $select = $this->tableGateway->getSql()->select();
        $select->where("c_id = {$data['companyId']}");
        $select->where("ar_id = {$data['roleId']}");
        $resultSet = $this->tableGateway->selectWith($select);

        foreach ($resultSet as $result) {
            $caraId = $result->ara_id;
        }

        $alias = $data['alias'];
        $aliasId = (int) $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->getAliasIdByAliasString($alias);

        if ($aliasId == 0) {
            $aliasId = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->saveAssessmentRoleAlias($alias);
        }

        $caraData['c_id'] = $data['companyId'];
        $caraData['ar_id'] = $data['roleId'];
        $caraData['ara_id'] = $aliasId;

        if ($aliasId != 0) {
            if ($caraId == 0) {
                $this->tableGateway->insert($caraData);
            } else {
                $this->tableGateway->update($caraData, array("c_id = {$data['companyId']}", "ar_id = {$data['roleId']}"));
            }
        }
    }
}
