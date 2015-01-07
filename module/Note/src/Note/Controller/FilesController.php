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

class FilesController extends AbstractActionController
{
    protected $fileTable;
    protected $noteFilesTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        return parent::onDispatch($e);
    }

    public function getFileTable()
    {
        if (!$this->fileTable) {
            $sm = $this->getServiceLocator();
            $this->fileTable = $sm->get('Application\Model\FilesTable');
        }
        return $this->fileTable;
    }

    public function getNotefilesTable()
    {
        if (!$this->noteFilesTable) {
            $sm = $this->getServiceLocator();
            $this->noteFilesTable = $sm->get('Note\Model\NotesFilesTable');
        }
        return $this->noteFilesTable;
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

        $docRoot = $_SERVER['SERVER_NAME'] == 'hipaa' ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/public';

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
            ob_clean();
            flush();
            readfile($filepath);
            exit;
        } else {
            echo "Sorry, such file doesn't exist";
            die;
        }
    }

}
