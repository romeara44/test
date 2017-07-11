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

class CompanyConsultantsTable implements ServiceLocatorAwareInterface
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

    public function getByCompany($cId)
    {
        if(!$cId) return array();

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('u' => 'users'), 'u.u_id = cc_consultant_id', array( '_u_id'        => new \Zend\Db\Sql\Expression('u.u_id')
                                                                               , '_u_firstname' => new \Zend\Db\Sql\Expression('u.u_firstname')
                                                                               , '_u_lastname'  => new \Zend\Db\Sql\Expression('u.u_lastname')), 'inner');
        $select->where("cc_company_id =" . $cId);

        return $this->tableGateway->selectWith($select);
    }

    public function deleteByCompanyId($cId)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('company_consultants')->where(array('cc_company_id' => $cId));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        return true;
   }

    public function saveCompanyConsultants($data)
    {
        $this->tableGateway->insert($data);

        return $this->tableGateway->lastInsertValue;;
    }

    public function getCompaniesIdsForConsultant($u_id)
    {
        if(!$u_id) return array();

        $select = $this->tableGateway->getSql()->select();
        $select->where("cc_consultant_id =" . $u_id);

        $res = $this->tableGateway->selectWith($select);
        $companies = array();
        foreach ($res as $row) {
            $companies[] = $row->cc_company_id;
        }
        return $companies;
    }

    public function getConsultantsIdsForCompany($c_id)
    {
        if(!$c_id) return array();

        $select = $this->tableGateway->getSql()->select();
        $select->where("cc_company_id =" . $c_id);

        $res = $this->tableGateway->selectWith($select);
        $companies = array();
        foreach ($res as $row) {
            $companies[] = $row->cc_consultant_id;
        }
        return $companies;
    }

}