<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssessmentRoleTable implements ServiceLocatorAwareInterface
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

    // TODO Update this to check for role_alias
    public function getRoleNameById($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('ar_id = ' . $id);
        $select->where('ar_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return '';
        }

        return $row->ar_name;
    }

    public function getAssessmentsRoles($aType = 1, $interview = false, $companyId = false)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ar_active = 1');

        if ($aType == 2) {
            $select->where('ar_id = 7');
        } else if($interview) {
            $select->where('ar_id < 7');
        } else {
            //$select->where('ar_id <> 7');
        }

        if ($companyId) {
            $t = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleAlias')->getAssessmentRoleAliases($companyId);
            $ts = array();

            foreach ($t as $index => $value) {
                $ts[$value['ar_id']] = 'test' . $value['ar_id'];
            }

        }

        $select->order('ar_order ASC');
        $resultSet = $this->tableGateway->selectWith($select);

        if ($companyId) {
            $resultSet->buffer();
            foreach ($resultSet as $i => $v) {
                if (array_key_exists($v->ar_id, $ts)) {
                    $results[] = array('ar_name' => $ts[$v->ar_id], 'ar_id' => $v->ar_id);
                } else {
                    $results[] = array('ar_name' => $v->ar_name, 'ar_id' => $v->ar_id);
                }
            }

            $resultSet->initialize($results);
        }

        return $resultSet;
    }
}