<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Assessment\Controller;

use Assessment\Model\AssessmentRoleAlias;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Assessment\Form\AssessmentForm;
use Assessment\Model\Assessment;
use Assessment\Model\AssessmentRoleLocationContact;
use Assessment\Form\AssessmentRoleAliasForm;
use Assessment\Model\AssessmentRoleAlias2;
use Assessment\Model\AssessmentInventoryLocationItem;
use Assessment\Model\AssessmentBusinessAssociateLocation;
use Assessment\Model\AssessmentQuestionOption;
use Admin\Model\User;
use Note\Model\Note;
use Mail\Model\Mailtemplate;
use Client\Form\ClientForm;
use Businessassociate\Model\Businessassociate;
use Businessassociate\Form\BusinessassociateForm;
use Zend\Session\Container;

class AliasController extends AbstractActionController
{
    protected $companyTable;
    protected $businessassociateTable;
    protected $assessmentTable;
    protected $remediationplanTable;
    protected $addressTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
//        var_dump('ytsert');
//        die();

        $this->layout()->searchRoleFilter = 'alias';
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5, 8))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }


//        var_dump($this);
//        die();
        return parent::onDispatch($e);
    }

    public function editAction()
    {
        $request = $this->getRequest();
//        $request->setMethod('post');

        if ($request->isPost()) {
            $post = $request->getPost();
            var_dump($post);
            die();
        } else {
//            var_dump('test');
//            die();
        }
//        $post = $request->getPost();
//        var_dump($post);
//            die();
//        } else {
//            var_dump($request->isPost());
//            $request->setMethod('post');
//            var_dump($request->isPost());
//            var_dump('not a post');
//            die();
//        }


        $viewParams['id'] = $this->params('id');
        $viewParams['cId'] = $this->params('companyId');
        $viewParams['defaultAlias'] = $this->getServiceLocator()
            ->get('Assessment\Model\AssessmentRoleTable')
            ->getRoleNameById($viewParams['id']);

        $hasAlias = $this->getServiceLocator()
            ->get('Assessment\Model\AssessmentRoleTable')
            ->getRoleNameById($viewParams['id'], $viewParams['cId']);

        $viewParams['alias'] = 'No alias is set for this role.';
        if ($hasAlias != 0) {
            $viewParams['alias'] = $hasAlias;
        }

        $viewModel = new ViewModel($viewParams);
//        var_dump($viewModel);
        return $viewModel;
    }

    public function listAction()
    {
//        return 'test';
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

}
