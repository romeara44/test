<?php
namespace Mail\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;

class MailsentTable
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

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function saveMail($mailData = array())
    {
        $this->tableGateway->insert($mailData);
        $id = $this->tableGateway->lastInsertValue;

        return $id;
    }

}