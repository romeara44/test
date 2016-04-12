<?php
namespace Client\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;

use Zend\Db\Sql\Expression;

class CompanyTrainingManagersTable implements ServiceLocatorAwareInterface
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

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getTrainingManagerCompaniesIds($u_id)
    {
        if(!$u_id) return array();

        $select = $this->tableGateway->getSql()->select();
        $select->where("ctm_u_id =" . $u_id);

        $res = $this->tableGateway->selectWith($select);
        $companies = array();
        foreach ($res as $row) {
            $companies[] = $row->ctm_company_id;
        }
        return $companies;
    }

    public function getTrainingManagersIdsForCompany($c_id)
    {
        if(!$c_id) return array();

        $select = $this->tableGateway->getSql()->select();
        $select->where("ctm_company_id =" . $c_id);

        $res = $this->tableGateway->selectWith($select);
        $users = array();
        foreach ($res as $row) {
            $users[] = $row->ctm_u_id;
        }
        return $users;
    }

    public function deleteTrainingManagerForCompany($cId, $uId)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('company_training_managers')->where(array('ctm_company_id' => $cId, 'ctm_u_id' => $uId));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        return true;
   }

   public function deleteTrainingManager($uId)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('company_training_managers')->where(array('ctm_u_id' => $uId));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        return true;
   }

    public function addTrainingManagerForCompany($cId, $uId)
    {
        $data = array(
            'ctm_company_id' => $cId,
            'ctm_u_id' => $uId,
        );

        $this->tableGateway->insert($data);

        return true;
    }
}