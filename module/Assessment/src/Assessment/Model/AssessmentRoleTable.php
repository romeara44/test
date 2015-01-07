<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

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

    public function getAssessmentsRoles($aType = 1)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ar_active = 1');

        if ($aType == 2) {
            $select->where('ar_id = 7');
        } else {
            $select->where('ar_id <> 7');
        }
        $select->order('ar_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }
}