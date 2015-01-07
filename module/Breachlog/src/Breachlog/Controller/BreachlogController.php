<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Breachlog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Breachlog\Form\BreachlogForm;
use Admin\Model\User;
use Breachlog\Model\Breachlog;
use Breachlog\Model\Breachloganswer;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;

class BreachlogController extends AbstractActionController
{
    protected $breachlogTable;
    protected $breachloganswerTable;
    protected $breachlogquestionTable;
    protected $userTable;
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
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        return parent::onDispatch($e);
    }

    public function getBreachlogTable()
    {
        if (!$this->breachlogTable) {
            $sm = $this->getServiceLocator();
            $this->breachlogTable = $sm->get('Breachlog\Model\BreachlogTable');
        }
        return $this->breachlogTable;
    }

    public function getBreachlogquestionTable()
    {
        if (!$this->breachlogquestionTable) {
            $sm = $this->getServiceLocator();
            $this->breachlogquestionTable = $sm->get('Breachlog\Model\BreachlogquestionTable');
        }
        return $this->breachlogquestionTable;
    }

    public function getBreachloganswerTable()
    {
        if (!$this->breachloganswerTable) {
            $sm = $this->getServiceLocator();
            $this->breachloganswerTable = $sm->get('Breachlog\Model\BreachloganswerTable');
        }
        return $this->breachloganswerTable;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
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

    public function listAction()
    {
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $mappingSortCol = array(
            'id' => 'bl_id',
            'name' => 'bl_name',
            'cId' => 'bl_c_id',
            'date' => 'bl_date_of_occurrence',
            'reportable' => 'bl_reportable'
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'bl_id';
        $paginator = $this->getBreachlogTable()->getBreachlogs(true, $sortCol, $order, $this->getIdentity());
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter
        ));

        return $view;
    }

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $noteform = $request->isPost() && (int) $request->getPost('noteform');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new BreachlogForm($this->getServiceLocator());
        $blObj = null;
        $userObj = null;

        if ((int) $id) {
            $blObj = $this->getBreachlogTable()->getBreachlog($id);
        }
        $questionsErrors = false;
        $answers = array();
        $questionsFormAnswers = array();
        $identity = $this->getIdentity();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $bl = new Breachlog();
            $post = $request->getPost();

            $ymd1 = \DateTime::createFromFormat('m/d/Y', $post['bl_date_of_occurrence']);
            if (is_object($ymd1)) {
                $ymd1 = $ymd1->format('Y-m-d');
            } else {
                $ymd1 = '';
            }
            $post['bl_date_of_occurrence'] = $ymd1;

            $form->setInputFilter($bl->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if (!(int) $id) { // check if all questions answered - only for new breach log
                if (isset($post['questions'])) {
                    foreach ($post['questions'] as $questionId => $question) {
                        $questionsFormAnswers[$questionId] = $question;
                    }
                    if (count($questionsFormAnswers) < 8) {
                        $questionsErrors = true;
                    }
                } else {
                    $questionsErrors = true;
                }
            }

            if ($form->isValid() && !$questionsErrors) {
                $post['bl_consultant_u_id'] = $identity['u_id'];

                $bl->exchangeArray($post);
                $this->getBreachlogTable()->setServiceLocator($this->getServiceLocator());
                $blId = $this->getBreachlogTable()->saveBreachlog($bl);

                // save answers
                if (isset($post['questions'])) {
                    foreach ($post['questions'] as $questionId => $question) {
                        $bla = new Breachloganswer();
                        $answer['bla_blq_id'] = $questionId;
                        $answer['bla_value'] = $question;
                        $answer['bla_bl_id'] = $blId;

                        $bla->exchangeArray($answer);
                        $this->getBreachloganswerTable()->setServiceLocator($this->getServiceLocator());
                        $blaId = $this->getBreachloganswerTable()->saveBreachloganswers($bla);
                    }
                    $this->getBreachlogquestionTable()->setServiceLocator($this->getServiceLocator());
                    $brpId = $this->getBreachlogquestionTable()->setReportable($blId);
                    if ($brpId) {
                        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
                    }
                }

                $this->flashMessenger()->addSuccessMessage('Breach log saved');

                return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));
            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                   // echo "Validation failure '$messageId': $message<br/>";
                }

                if ((int) $id) {
                    $form->bind($blObj);
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);

            if ((int) $id) {
                $blObj->bl_date_of_occurrence = ($blObj->bl_date_of_occurrence != '0000-00-00 00:00:00') ? substr($blObj->bl_date_of_occurrence, 0, 10) : '';
                $form->bind($blObj);
            }
        }

        $questions = $this->getBreachlogquestionTable()->getBreachlogquestionsWithAnswers($id);

        return array(
            'form' => $form,
            'blId' => $id,
            'blObj' => $blObj,
            'questions' => $questions,
            'questionsFormAnswers' => $questionsFormAnswers,
            'questionsErrors' => $questionsErrors
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getBreachlogTable()->deleteBreachlog($id);
        $this->flashMessenger()->addSuccessMessage('Breach log has been deleted');

        return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getBreachlogTable()->unarchiveBreachlog($id);
        $this->flashMessenger()->addSuccessMessage('Breach log has been deleted');

        return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));

    }
}
