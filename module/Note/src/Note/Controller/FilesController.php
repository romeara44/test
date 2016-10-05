<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Note\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use DataCrypt\FileCrypt;

class FilesController extends AbstractActionController
{
    protected $fileTable;
    protected $noteFilesTable;
    protected $noteTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        $identity = $this->getIdentity();

        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getFileTable()
    {
        if (!isset($this->fileTable)) {
            $sm = $this->getServiceLocator();
            $this->fileTable = $sm->get('Application\Model\FilesTable');
        }
        return $this->fileTable;
    }

    public function getNotefilesTable()
    {
        if (!isset($this->noteFilesTable)) {
            $sm = $this->getServiceLocator();
            $this->noteFilesTable = $sm->get('Note\Model\NotesFilesTable');
        }
        return $this->noteFilesTable;
    }

    public function getNoteTable()
    {
        if (!isset($this->noteTable)) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function getAction()
    {
        $type = $this->params('type');
        $noteId = (int) $this->params('note_id');
        $fId = (int) $this->params('file_id');
        if (!$this->hasIdentity()) {
            $container = new Container('files');
            $container->item = '/files/get/note/' . $noteId . '/' . $fId;

            return $this->redirect()->toRoute('application', array('controller' => 'application', 'action' => 'index'));
        }

        $docRoot = $_SERVER['DOCUMENT_ROOT'];

        $filename = '';
        $filetype = '';

        if ($type == 'note') {
            $file = $this->getFileTable()->getFile($fId);

            $filepath = $docRoot . '/data/notefiles/' . $noteId . '/' . $fId;
            if (!file_exists($filepath)) {
                $noteId = $this->getNotefilesTable()->getFileNoteByFId($fId);
                $filepath = $docRoot . '/data/notefiles/' . $noteId . '/' . $fId;
            }

            $filename = $file->f_name;
            $filetype = $file->f_type;
        } elseif ($type == 'report') {
            $file = $this->getFileTable()->getFile($fId);

            $filepath = $docRoot . '/data/reports/' . $noteId . '/' . $fId;
            if (!file_exists($filepath)) {
                $noteId = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getFileByFId($fId);
                $filepath = $docRoot . '/data/reports/' . $noteId . '/' . $fId;
            }
            $filename = $file->f_name;
            $filetype = $file->f_type;
        } elseif ($type == 'it_asset') {
            $file = $this->getFileTable()->getFile($fId);

            $filepath = $docRoot . '/data/it_asset/' . $noteId . '/' . $fId;
            if (!file_exists($filepath)) {
                $noteId = $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryReportTable')->getFileByFId($fId);
                $filepath = $docRoot . '/data/it_asset/' . $noteId . '/' . $fId;
            }
            $filename = $file->f_name;
            $filetype = $file->f_type;
        } elseif ($type == 'ba_report') {
            $file = $this->getFileTable()->getFile($fId);

            $filepath = $docRoot . '/data/ba_reports/' . $noteId . '/' . $fId;
            $filename = $file->f_name;
            $filetype = $file->f_type;
        }

        $tmpfile = $docRoot . '/data/tmp/' . $filename;

        if($file->f_encrypted) {
            if(FileCrypt::decrypt($filepath, $tmpfile)) {
                $filepath = $tmpfile;
            } else {
                echo "Sorry, such file doesn't exist";
                die;
            }
        }

        if ($fId > 0 && (file_exists($filepath))) {
            
            header('Content-Description: File Transfer');
            if ($filetype) {
                header('Content-Type: ' . $filetype);
            } else {
                header('Content-Type: application/octet-stream');
            }
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            @ob_clean();
            @flush();
            readfile($filepath);
            if($file->f_encrypted) {
                unlink($filepath);
            }
            exit;
        } else {
            echo "Sorry, such file doesn't exist";
            die;
        }
    }

    public function deleteAction()
    {
        $noteId = (int) $this->params('note_id');
        $fId = (int) $this->params('file_id');
        if (!$this->hasIdentity()) {
            exit;
        }

        $docRoot = $_SERVER['DOCUMENT_ROOT'];

        $filepath = $docRoot . '/data/notefiles/' . $noteId . '/' . $fId;

        if ($fId > 0 && (file_exists($filepath))) {            
            $this->getNotefilesTable()->deleteFileByFId($fId);
            $this->getFileTable()->deleteFileById($fId);
            unlink($filepath);
            $files = $this->getNotefilesTable()->getFilesByNoteId($noteId);
            if ($files->count()) {
                echo '1';
            } else {
                $this->getNoteTable()->deleteNote($noteId);
                echo '0';
            }
            
            exit;
        } else {
            echo "Sorry, such file doesn't exist";
            die;
        }
    }
}
