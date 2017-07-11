<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class GetConsultant extends AbstractHelper
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

    public function __invoke($sl)
    {
        $user = '';
        if (!$this->hasIdentity()) {
            return '';
        }

        $identity = $this->getIdentity();

        if ($identity['u_role_id'] == 6) {
            $businessassociateTable = $sl->get('Businessassociate\Model\BusinessassociateTable');
            $ba = $businessassociateTable->getBusinessassociateByContactId($identity['u_id']);

            $userTable = $sl->get('Admin\Model\UserTable');
            $user = $userTable->getUser($ba->ba_consultant_u_id);
        } elseif (in_array($identity['u_role_id'], array(5,8))) {
            $company_consultants = $sl->get('Client\Model\CompanyConsultantsTable')->getByCompany($identity['u_company_id']);
            foreach ($company_consultants as $value) {
                $user = $sl->get('Admin\Model\UserTable')->getUser($value->cc_consultant_id);
                break;
            }            
        }

        return $user;
    }


}