<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class BodyClass extends AbstractHelper
{
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

    public function __invoke()
    {
        if (!$this->hasIdentity()) {
            return '';
        }

        $identity = $this->getIdentity();

        if ($identity['u_role_id'] == 1) {
            return 'admin';
        } elseif ($identity['u_role_id'] == 2) {
            return 'consultant';
        } elseif ($identity['u_role_id'] == 3) {
            return 'consultant';
        } elseif ($identity['u_role_id'] == 4) {
            return 'sales-rep';
        } elseif ($identity['u_role_id'] == 5) {
            return 'client';
        } elseif ($identity['u_role_id'] == 6) {
            return 'ba';
        }


        return '';
    }


}