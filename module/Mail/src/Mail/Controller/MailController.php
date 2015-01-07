<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Mail\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\Session\Container;

use Admin\Model\User;
use Mail\Model\Mailtemplate;

class MailController extends AbstractActionController
{
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        return parent::onDispatch($e);
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

    public function sendAction()
    {
        $request = $this->getRequest();

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $identity = $this->getIdentity();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $text = nl2br($post['text']);

            //$htmlTemplateText = $this->_getHtmlTemplate($text);
            //$this->getMailtemplateTable()->setServiceLocator($this->getServiceLocator());
            $this->getMailtemplateTable()->sendMail($this->getServiceLocator(), array('subject' => $post['subject'], 'text' => $text, 'addto' => $post['addto'], 'post' => $post));
        }
    }

    public function _getHtmlTemplate($content)
    {
        $renderer = new PhpRenderer();

        $docRoot = $_SERVER['SERVER_NAME'] == 'hipaa' ? $_SERVER['DOCUMENT_ROOT'] . '/..' : $_SERVER['DOCUMENT_ROOT'];

        $map = new Resolver\TemplateMapResolver(array(
            'mail/mailtemplate' => $docRoot . '/module/Mail/view/mail/mail/mailtemplate.phtml',
        ));

        $renderer->setResolver($map);

        $identity = $this->getIdentity();

        $consultant = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($identity['u_id']);
        $consultantName = $consultant->u_firstname . ' ' . $consultant->u_firstname;
        $consultantEmail = $consultant->u_email;
        $consultantPhone = $consultant->u_office_phone;

        $model = new ViewModel(array(
            'content' => $content,
            'consultant_name' => $consultantName,
            'consultant_email' => $consultantEmail,
            'consultant_phone' => $consultantPhone,
            'consultant_role' => $this->getServiceLocator()->get('Admin\Model\RoleTable')->getRoleName($identity['u_role_id']),
        ));
        $model->setTemplate('mail/mailtemplate');

        $html = $renderer->render($model);

        return $html;
    }
}
