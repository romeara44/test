<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class AsessmentProgressBar extends AbstractHelper
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

    public function __invoke($a)
    {
        if (!$this->hasIdentity()) {
            return '';
        }

        $progressNum = 0;
        if ($a->a_step1_finished == 1) {
            $progressNum = 1;
        }
        if ($a->a_step2_finished == 1) {
            $progressNum = 2;
        }
        if ($a->a_step3_finished == 1) {
            $progressNum = 3;
        }
        if ($a->a_step4_finished == 1) {
            $progressNum = 4;
        }
        if ($a->a_step5_finished == 1) {
            $progressNum = 5;
        }


        if ($a->a_status == \Assessment\Model\Assessment::STATUS_CLOSED) {
            return '<span class="fleft">Closed</span>';
        } elseif ($a->a_all_steps_finished) {
            return '<span class="fleft">Finished</span>';
        } else {
            if ($a->a_type == 1) {
                return '<span class="fleft">In Progress</span> <span class="progress-bar' . $progressNum . '"></span>';
            }  else {
                return '<span class="fleft">In Progress</span>';
            }
        }
    }


}