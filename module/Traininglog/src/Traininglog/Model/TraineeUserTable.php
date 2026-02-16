<?php
namespace Traininglog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;

//use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
//use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Distinct;



class TraineeUserTable implements ServiceLocatorAwareInterface
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

    // public function getOpenAuditRecords($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    // {
    //     if ($paginated) {
    //         $select = new Select('audit_record');
    //         $resultSetPrototype = new ResultSet();
    //         $resultSetPrototype->setArrayObjectPrototype(new AuditRecord());
    //         $paginatorAdapter = new DbSelect(
    //             $select,
    //             $this->tableGateway->getAdapter(),
    //             $resultSetPrototype
    //         );

    //         $paginator = new Paginator($paginatorAdapter);

    //         return $paginator;
    //     }

    //     $select = $this->tableGateway->getSql()->select();
    //     $select->where('audit_status = 0');

    //     $resultSet = $this->tableGateway->selectWith($select);

    //     return $resultSet;

    // }


    public function saveTraineeUserRecord(TraineeUser $trainee_user)
    {
        $id = (int)$trainee_user->trainee_user_id;

        $data = array();
        $traineeArray = (array) $trainee_user;
        
        if ($id == 0)
        {
            $this->tableGateway->insert($traineeArray);
            $id = $this->tableGateway->lastInsertValue;
        }
        else
        {
            if ($this->getTraineeUser($id))
            {
                $this->tableGateway->update($traineeArray, array('trainee_user_id' => $id));
            }
            else
            {
                throw new \Exception('Audit Record Id does not exist');
            }

        }

        return $id;
    }
    public function saveTraineeUserRecordArray($data)
    {
        $id = (int)$data['trainee_user_id'];
        
        if ($id == 0)
        {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }
        else
        {
            if ($this->getTraineeUser($id))
            {
                $this->tableGateway->update($data, array('trainee_user_id' => $id));
            }
            else
            {
                throw new \Exception('Audit Record Id does not exist');
            }

        }

        return $id;
    }

    public function deleteTraineeUsersByCompany($companyName)
    {
        
        // $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        // $sql          = new Sql( $db );
        // //$delete       = $sql->delete('trainee_user')->where('company_name = ' . "'" . $companyName . "'");
        // $delete       = $sql->delete('trainee_user')->where->equalTo('company_name', $companyName);
        // //$delete->where->equalTo('goal_id', $edit_id);
        // $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        // $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        // return true;

        // return $resultSet;

        $action = new Delete('trainee_user');
        $action->where(array('company_name = ?' => $companyName));
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');

        $sql    = new Sql($db);
        $stmt   = $sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();

        return (bool)$result->getAffectedRows();
   }

    public function getDepartmentsByCompany($companyName){
        
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array( "custom_field_1"));
        $select->where('company_name = ' . "'" . $companyName . "'");
        $resultSet = $this->tableGateway->selectWith($select);

        $departments = array();
        foreach ($resultSet as $rs) {
            array_push($departments, $rs->custom_field_1);
        }
        $departmentsUnique = array_unique($departments, SORT_STRING);
        asort($departmentsUnique, SORT_STRING);
        return $departmentsUnique;
        
    }

    public function getTraineeUser($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('trainee_user_id = ' . $id);
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    


}