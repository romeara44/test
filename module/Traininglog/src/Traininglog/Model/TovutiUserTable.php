<?php
// Filename: /module/Traininglog/src/traininglog/Model/TovutiUserTable.php

namespace Traininglog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Distinct;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;


class TovutiUserTable implements ServiceLocatorAwareInterface
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

    public function getTovutiUserFomDatabase($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('tovuti_id = ' . $id);
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveTovutiUserRecord(TovutiUser $tovuti_user)
    {
        
        $id = (int)$tovuti_user->tovuti_user_id;
        $tovutiid = (int)$tovuti_user->tovuti_id;

        $tovutiUserArray = (array) $tovuti_user;
        
        if ($id == 0)
        {
            $this->tableGateway->insert($tovutiUserArray);
            $id = $this->tableGateway->lastInsertValue;
        }
        else
        {
            // if ($this->getTovutiUserFomDatabase($tovutiid))
            // {
            //      $response = $this->tableGateway->update($tovutiUserArray, array('tovuti_id' => $tovutiid));
            // }
            if ($this->getTovutiUserFomDatabase($tovutiid))
            {
                 $response = $this->tableGateway->update($tovutiUserArray, array('tovuti_user_id' => $id));
            }
            else
            {
                throw new \Exception('Tovuti User Id does not exist');
            }
        }

        return $id;
    }

    //Used to update all user information from Tovuti into Carosh
    public function getAllTovutiUser()
    {
        $select = $this->tableGateway->getSql()->select();
        //$select->where('user_group_id = 0');

        $orders[] = 'first_name ASC';
        $orders[] = 'last_name ASC';
        

        $select->order($orders);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    //Used to update all user information from Tovuti into Carosh
    public function getAllTovutiUsersByCompanyName($companyName)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where("company_name = '{$companyName}'");

        $orders[] = 'first_name ASC';
        $orders[] = 'last_name ASC';
        
        $select->order($orders);

        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    //Used to update all user information from Tovuti into Carosh
    public function getAllDepartmentsByCompanyName($companyName)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('department'));
        $select->where("company_name = '{$companyName}'");
        $select->group('company_name');
        $select->group('department');

        $resultSet = $this->tableGateway->selectWith($select);
        
        $departments = array();
        $indexItem = 0;
        foreach ($resultSet as $rs) {
            if (isset($rs->department)) {
                $departments[$indexItem] = $rs->department;
                $indexItem++;
            }
        }
        
        return $departments;
    }

    //Used to update all user information from Tovuti into Carosh
    public function getAllJobTitlesByCompanyName($companyName)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('job_title'));
        $select->where("company_name = '{$companyName}'");
        $select->group('company_name');
        $select->group('job_title');

        $resultSet = $this->tableGateway->selectWith($select);
        $jobTitles = array();
        $indexItem = 0;
        foreach ($resultSet as $rs) {
            if (isset($rs->job_title)) {
                $jobTitles[$indexItem] = $rs->job_title;
                $indexItem++;
            }
        }

        return $jobTitles;
    }
}