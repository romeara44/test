<?php
namespace Note\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;
use DataCrypt\FileCrypt;
use DataCrypt\DbCrypt;

class NoteTable implements ServiceLocatorAwareInterface
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

    public function getNotes($itemId = 0, $itemType = 1, $subItemId = 0, $type = Null)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('note_id'          => 'note_id',
                               'note_text'        => new \Zend\Db\Sql\Expression('IF(note_encrypted,' . DbCrypt::decryptField('note_text', false) . ', note_text)'),
                               'note_u_id'        => 'note_u_id',
                               'note_item_type'   => 'note_item_type',
                               'note_item_id'     => 'note_item_id',
                               'note_subitem_id'  => 'note_subitem_id',
                               'note_active'      => 'note_active',
                               'note_create_date' => 'note_create_date',
                               'note_encrypted'   => 'note_encrypted'
                              )
                            );

        $select->join(array('u' => 'users'), 'note_u_id = u_id', array('_username' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_note_create_date_format' => new \Zend\Db\Sql\Expression("DATE_FORMAT(note_create_date, '%b %D, %Y')")));

        $select->join(array('nf' => 'notes_files'), 'note_id = nf_note_id', array('*', '_files' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(CONCAT(f_name, "::", f_id))')), 'left');
        $select->join(array('f' => 'files'), 'nf_f_id = f_id', array('*'), 'left');

        $select->where('note_item_type = ' . $itemType);
        $select->where('note_item_id = ' . $itemId);

        if($type == 'text') {
            $select->where("nf_note_id IS NULL");
        } else if($type == 'file') {
            $select->where("nf_note_id IS NOT NULL");
        }
        
        if ($subItemId) {
            $select->where('note_subitem_id = ' . $subItemId);
        }

        $select->group(array('note_id'));

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getNotesForRemedTask($rpaId, $subItemId, $type = Null)
    {
        $rpa = $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable')->getRemediationplanaction($rpaId);
        $rp = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplan($rpa->rpa_rp_id);
        if(!$rp->rp_a_id) {
            return array();
        }
        $ids = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestionsIdsByCategory($rpa->rpa_aqc_id);
        $answersIds = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->getAnswersIdsByQuestion($rp->rp_a_id, $subItemId, $ids);
        $answersIds[] = 0;
        /*
        echo '<pre>';
        print_r($ids);
        die;
        echo 'rpaId ' . $rpa->rpa_aqc_id;
        die;*/
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('u' => 'users'), 'note_u_id = u_id', array('_username' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_note_create_date_format' => new \Zend\Db\Sql\Expression("DATE_FORMAT(note_create_date, '%b %D, %Y')")));

        $select->join(array('nf' => 'notes_files'), 'note_id = nf_note_id', array('*', '_files' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(CONCAT(f_name, "::", f_id))')), 'left');
        $select->join(array('f' => 'files'), 'nf_f_id = f_id', array('*'), 'left');

        $select->where('note_item_type = 8');
        $select->where('note_item_id IN(' . implode(',', $answersIds). ')');

        $select->where('note_subitem_id = ' . $subItemId); // adr id

        if($type == 'text') {
            $select->where("nf_note_id IS NULL");
        } else if($type == 'file') {
            $select->where("nf_note_id IS NOT NULL");
        }
        
        $select->group(array('note_id'));

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getNote($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('note_id'          => 'note_id',
                               'note_text'        =>  new \Zend\Db\Sql\Expression('IF(note_encrypted,' . DbCrypt::decryptField('note_text', false) . ', note_text)'),
                               'note_u_id'        => 'note_u_id',
                               'note_item_type'   => 'note_item_type',
                               'note_item_id'     => 'note_item_id',
                               'note_subitem_id'  => 'note_subitem_id',
                               'note_active'      => 'note_active',
                               'note_create_date' => 'note_create_date',
                               'note_encrypted'   => 'note_encrypted'
                              )
                            );

        $select->join(array('u' => 'users'), 'note_u_id = u_id', array('_username' => 'CONCAT(u_firstname, " ", u_lastname)'));

        $select->where('note_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveNote(Note $note, $files = null, $isCopy = false, $fileName = 'notesFiles', $encrypt = false)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!$isCopy && $files) {
            if ((trim($note->note_text) == '') && (!isset($files[$fileName]) || count($files[$fileName]) == 0 || $files[$fileName][0]['tmp_name'] == '')) {
                return;
            }
        }

        $data = array(
            'note_text' => $encrypt ? DbCrypt::encryptValue($note->note_text) : $note->note_text,
            'note_u_id' => $identity['u_id'],
            'note_item_type' => $note->note_item_type,
            'note_item_id' => $note->note_item_id,
            'note_encrypted' => $encrypt ? 1 : 0,
        );

        if ($note->note_subitem_id) {
            $data['note_subitem_id'] = $note->note_subitem_id;
        }

        if ($isCopy) {
            $data['note_create_date'] = $note->note_create_date;
        }

        $id = (int) $note->note_id;
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getNote($id)) {
                $this->tableGateway->update($data, array('note_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        if($files) $this->_saveFiles($id, $files, $fileName, $encrypt);

        return $id;
    }

    public function _saveFiles($id, $files, $fileName, $encrypt = false)
    {
        if (!isset($files[$fileName])) {
            return;
        }

        $notesFolder = 'public/data/notefiles';
        if (!is_dir($notesFolder)) {
            mkdir($notesFolder);
        }

        if (!is_dir($notesFolder . '/' . $id)) {
            mkdir($notesFolder . '/' . $id);
        }

        $filesTable = $this->getServiceLocator()->get('Application\Model\FilesTable');
        $notesFilesTable = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');

        if (isset($files[$fileName])) {
            foreach ($files[$fileName] as $file) {

                if ($file['tmp_name'] == '') continue;

                $dataFile = array();

                $tempFile = $file['tmp_name'];

                $dataFile['f_name']      = $file['name'];
                $dataFile['f_type']      = $file['type'];
                $dataFile['f_encrypted'] = $encrypt ? 1 : 0;

                $fId = $filesTable->saveFile($dataFile);

                $notesDataFile = array();
                $notesDataFile['nf_f_id'] = $fId;
                $notesDataFile['nf_note_id'] = $id;

                $notesFilesTable->saveFile($notesDataFile);

                $ret = $encrypt ? FileCrypt::encrypt($tempFile, $notesFolder . '/' . $id . '/' . $fId) : move_uploaded_file($tempFile, $notesFolder . '/' . $id . '/' . $fId);
            }
        }

        return true;
    }

    public function deleteAddress($id)
    {
        $data['note_id'] = $id;
        $data['note_active'] = 0;
        $this->tableGateway->update($data, array('note_id' => $id));

        return true;
    }
}