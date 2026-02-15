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

    public function cloneFile($sourceFile) {
        // Get the source file from the files table record
        //$sourceFile = $this->getServiceLocator()->get('Application\Model\FilesTable')->getFile($sourceAuditRecordFile->nf_f_id);
                                    
        $newFile = array();
        $newFile['f_name'] = $sourceFile->f_name;
        $newFile['f_type'] = $sourceFile->f_type;
        $newFile['f_create_date'] = $sourceFile->f_create_date;
        $newFile['f_encrypted'] = $sourceFile->f_encrypted;
        
        //$savedFileId = $this->getServiceLocator()->get('Application\Model\FilesTable')->saveFile($newFile);
        $savedFileId = $this->saveFile($newFile);

        return $savedFileId;
    }

}