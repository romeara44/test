<?php

namespace Trainee\Repository;

use Trainee\Entity\Post;
use Trainee\Entity\Department;
use Trainee\Entity\Hydrator\DepartmentHydrator;
use Zend\Stdlib\Hydrator\Aggregate\AggregateHydrator;
use Zend\Db\ResultSet\HydratingResultSet;

class PostRepository implements IPostRepository
{
    protected $dbAdapter;

    public function save(Post $post)
    {
        $sql = new \Zend\Db\Sql\Sql($this->dbAdapter);
        $insert = $sql->insert()
            ->values(array(
                'artist' => "SammyDavis",
                'title' => "Things"
            ))
            ->into('album');

        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }

    
    public function getDepartmentsByCompany($companyName){
        
        // $select = $this->tableGateway->getSql()->select();
        // $select->columns(array( "custom_field_1"));
        // $select->where('company_name = ' . "'" . $companyName . "'");
        // $resultSet = $this->tableGateway->selectWith($select);

        // $departments = array();
        // foreach ($resultSet as $rs) {
        //     array_push($departments, $rs->custom_field_1);
        // }
        // $departmentsUnique = array_unique($departments, SORT_STRING);
        // asort($departmentsUnique, SORT_STRING);
        // return $departmentsUnique;
        $sql = new \Zend\Db\Sql\Sql($this->dbAdapter);
        $select = $sql->select();
        $select->columns(array(
           'custom_field_1'
        ))
            ->from('trainee_user')
            ->where('company_name = ' . "'" . $companyName . "'")
            ->group('custom_field_1')
            ->order('custom_field_1 ASC');

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $hydrator = new AggregateHydrator();
        $hydrator->add(new DepartmentHydrator());

        $resultSet = new HydratingResultSet($hydrator, new Department());
        $resultSet->initialize($result);
        $depts = array();

        foreach($resultSet as $rs1)
        {
            $depts[] = $rs1;
        }
        
        return $depts;
    }


    public function setDbAdapter($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }
}