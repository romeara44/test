<?php
namespace Note\Model;
use Zend\Db\TableGateway\TableGateway;

class NotesFilesTable
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

    public function deleteFileByFId($id)
    {
        $this->tableGateway->delete('nf_f_id = ' . $id);
    }

    public function getFilesByNoteId($noteId = 0)
    {
        $noteId  = (int) $noteId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('nf_note_id = ' . $noteId);
        $select->where('nf_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getFileNoteByFId($fId = 0)
    {
        $fId  = (int) $fId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('nf_f_id = ' . $fId);
        $select->order('nf_id ASC');
        $select->limit(1);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return $row['nf_note_id'];
    }

    public function archiveFileByFId($id)
    {
        $this->tableGateway->update(array('nf_active' => 0), array('nf_f_id' => $id));
    }

    public function unarchiveFileByFId($id)
    {
        $this->tableGateway->update(array('nf_active' => 1), array('nf_f_id' => $id));
    }
}