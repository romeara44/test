<?php
namespace Application\Model;
use Zend\Db\TableGateway\TableGateway;

class FilesTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function saveFile($dataFile)
    {
        $this->tableGateway->insert($dataFile);
        $id = $this->tableGateway->lastInsertValue;

        return $id;
    }

    public function getFile($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('f_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function deleteFileById($id)
    {
        $this->tableGateway->delete('f_id = ' . $id);
    }
}