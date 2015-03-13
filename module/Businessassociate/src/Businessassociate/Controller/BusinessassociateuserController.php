<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Businessassociate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Admin\Model\User;
use Businessassociate\Model\Businessassociateanswer;
use Note\Model\Note;
use Zend\Session\Container;

class BusinessassociateuserController extends AbstractActionController
{
    protected $businessassociatequestionTable;
    protected $businessassociateanswerTable;
    protected $noteTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(6))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getBusinessassociatequestionTable()
    {
        if (!$this->businessassociatequestionTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociatequestionTable = $sm->get('Businessassociate\Model\BusinessassociatequestionTable');
        }
        return $this->businessassociatequestionTable;
    }

    public function getBusinessassociateanswerTable()
    {
        if (!$this->businessassociateanswerTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateanswerTable = $sm->get('Businessassociate\Model\BusinessassociateanswerTable');
        }
        return $this->businessassociateanswerTable;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getMailtemplateTable()
    {
        if (!$this->mailtemplateTable) {
            $sm = $this->getServiceLocator();
            $this->mailtemplateTable = $sm->get('Mail\Model\MailtemplateTable');
        }
        return $this->mailtemplateTable;
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

    public function questionsformAction()
    {
        $questions = $this->getBusinessassociatequestionTable()->getBusinessassociatequestions();

        $request = $this->getRequest();

        $identity = $this->getIdentity();
        $checkIfUserAnswered = (int) $this->getBusinessassociateanswerTable()->checkIfUserAnswered($identity['u_id']);
        $answers = array();

        $viewModel = new ViewModel();

        $answers = array();
        $ba = array();
        //if (!$checkIfUserAnswered) {
            $viewModel->setTemplate('businessassociate/businessassociateuser/questionsform.phtml');
            if ($request->isPost()) {
                $post = $request->getPost();

                $files = $request->getFiles();

                if (isset($post['questions'])) {
                    $this->getBusinessassociateanswerTable()->deleteBusinessassociateanswers($post['baId']);
                    foreach ($post['questions'] as $questionId => $question) {
                        $notesFiles = isset($files['notesFiles'][$questionId]) ? $files['notesFiles'][$questionId] : array();

                        $baa = new Businessassociateanswer();
                        $answer['baa_baq_id'] = $questionId;
                        $answer['baa_value'] = $question;
                        $answer['baa_ba_id'] = $post['baId'];
                        $baa->exchangeArray($answer);
                        $this->getBusinessassociateanswerTable()->setServiceLocator($this->getServiceLocator());
                        $baaId = $this->getBusinessassociateanswerTable()->saveBusinessassociateanswers($baa);

                        // save note to answer
                        $note = new Note();

                        $postNote['note_text'] = $post['notes'][$questionId];
                        $postNote['note_item_type'] = \Note\Model\Note::NOTE_BUSINESSASSOCIATE_ANSWER;
                        $postNote['note_item_id'] = $baaId;
                        $note->exchangeArray($postNote);

                        $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                        $noteId = $this->getNoteTable()->saveNote($note, array('notesFiles' => $notesFiles));
                    }
                }

                return $this->redirect()->toRoute('businessassociateuser', array('controller' => 'businessassociateuser', 'action' => 'questionsform'));
            } else {
                $ba = $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociateTable')->getBusinessassociateByContactId($identity['u_id']);
                $checkIfUserAnswered = (int) $this->getBusinessassociateanswerTable()->checkIfAnswersExists($ba->ba_id);
                if ($checkIfUserAnswered) {
                    $answers = $this->getBusinessassociateanswerTable()->getBaAnswers($ba->ba_id);
                }
            }
        //}
        /* else {
            $answers = $this->getBusinessassociateanswerTable()->getAnswers($identity['u_id']);

            $viewModel->setVariable('answers', $answers);
            $viewModel->setTemplate('businessassociate/businessassociateuser/questionsanswers.phtml');
        }*/

        $viewModel->setVariables(array(
            'questions' => $questions,
            'answers' => $answers,
            'signoff' => isset($ba->ba_status) && ($ba->ba_status == 1) ? 1 : 0,
            'baId' => $ba->ba_id,
        ));

        return $viewModel;
    }


}
