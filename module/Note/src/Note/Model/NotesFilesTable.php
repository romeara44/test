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

    // public function cloneNoteFiles($noteId, Note $note)
    // {
    //     //Audit Record Note Files
    //     $sourceFiles = $this->getFilesByNoteId($note->note_id);
    //     foreach ($sourceFiles as $sourceFile) {

    //         // Get the source file from the files table record
    //         $sourceFile = $this->getServiceLocator()->get('Application\Model\FilesTable')->getFile($sourceFile->nf_f_id);
            
    //         $newFile = array();
    //         $newFile['f_name'] = $sourceFile->f_name;
    //         $newFile['f_type'] = $sourceFile->f_type;
    //         $newFile['f_create_date'] = $sourceFile->f_create_date;
    //         $newFile['f_encrypted'] = $sourceFile->f_encrypted;
            
    //         $savedFileId = $this->getServiceLocator()->get('Application\Model\FilesTable')->saveFile($newFile);

    //         $newNoteFile = array();
    //         $newNoteFile['nf_f_id'] = $savedFileId;
    //         $newNoteFile['nf_note_id'] = $noteId;
    //         $newNoteFile['nf_active'] = $sourceFile->nf_active;
            
    //         $savedNoteFileId = $this->saveFile($newNoteFile);

    //         $notesFolder = 'public/data/notefiles';

    //         mkdir($notesFolder . '/' . $noteId);
    //         copy($notesFolder . '/' . $sourceFile->nf_note_id . '/' . $sourceFile->f_id, $notesFolder . '/' . $noteId . '/' . $savedFileId);

    //     }
    // }

    public function cloneNoteFile($savedFileId, $savedAuditNoteId, $sourceAuditRecordFile) {

        $newNoteFile = array();
        $newNoteFile['nf_f_id'] = $savedFileId;
        $newNoteFile['nf_note_id'] = $savedAuditNoteId;
        $newNoteFile['nf_active'] = $sourceAuditRecordFile->nf_active;
        
        //$savedNoteFileId = $this->getServiceLocator()->get('Note\Model\NotesFilesTable')->saveFile($newNoteFile);
        $savedNoteFileId = $this->saveFile($newNoteFile);

        return $savedNoteFileId;
    }

    public function clonePhysicalFile($savedAuditNoteId, $sourceAuditRecordFile, $sourceFile, $savedFileId) {

        $notesFolder = 'public/data/notefiles';

        mkdir($notesFolder . '/' . $savedAuditNoteId);
        copy($notesFolder . '/' . $sourceAuditRecordFile->nf_note_id . '/' . $sourceFile->f_id, $notesFolder . '/' . $savedAuditNoteId . '/' . $savedFileId);
    }
                        
}