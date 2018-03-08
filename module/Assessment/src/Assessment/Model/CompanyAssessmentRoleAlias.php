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

    public function saveCompanyAssessmentRoleAlias($data)
    {
        // TODO this fails poorly
        $araId = 0;

        $select = $this->tableGateway->getSql()->select();
        $select->where("c_id = {$data['companyId']}");
        $select->where("ar_id = {$data['roleId']}");
        $resultSet = $this->tableGateway->selectWith($select);

        foreach ($resultSet as $result) {
            $araId = $result->ara_id;
        }

        $alias = $data['alias'];


        if ($araId == 0) {
            $aliasId = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->getAliasIdByAliasString($alias);
            if ($aliasId == 0) {
                $aliasId = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->saveAssessmentRoleAlias($alias);
            }

            // TODO This is kinda confusing, rework it for clarity
            if ($aliasId != 0) {
                $caraData['c_id'] = $data['companyId'];
                $caraData['ar_id'] = $data['roleId'];
                $caraData['ara_id'] = $aliasId;
                $this->tableGateway->insert($caraData);
            }

        } else {
            // TODO INSERT NEW MAPPING HERE
            // Insert/Fetch alias ID
            $aliasId = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->getAliasIdByAliasString($alias);
            if ($aliasId == 0) {
                $insertId = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->saveAssessmentRoleAlias($alias);
            }

            $caraData['c_id'] = $data['companyId'];
            $caraData['ar_id'] = $data['roleId'];
            $caraData['ara_id'] = $aliasId;
            $this->tableGateway->update($caraData, array("c_id = {$data['companyId']}", "ar_id = {$data['roleId']}"));
        }

//        return $id;
    }

}