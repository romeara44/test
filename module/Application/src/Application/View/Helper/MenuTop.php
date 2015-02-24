<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class MenuTop extends AbstractHelper
{
    private $sl;
    private $items = array();

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
        if ($this->_checkIfAllowRenderMenu($sl)) {
            //return '';
        }

        if (!$this->hasIdentity()) {
            return '';
        }

        $this->_prepareItems();
        $this->_setActive($sl);

        $model = new ViewModel(array('items' => $this->items));
        $model->setTemplate('application/partials/menutop');

        return $this->getView()->render($model);
    }

    private function _prepareItems()
    {
        $identity = $this->getIdentity();

        if ($identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN) { // items for logged in
            $this->_prepareItemsForAdmin();
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_SENIOR_CONSULTANT) {
            $this->_prepareItemsForConsultant();
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_CONSULTANT) {
            $this->_prepareItemsForConsultant();
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_CLIENT) {
            $this->_prepareItemsForClient();
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_SALES_REP) {
            $this->_prepareItemsForSalesRep();
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_BUSINESS_ASSOCIATE) {
            // nothing
        } elseif ($identity['u_role_id'] == \Admin\Model\User::ROLE_PARTIAL) {
            $this->_prepareItemsForPartial();
        }
    }

    private function _prepareItemsForAdmin()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/admin',
                'items' => array(
                )
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list'
            ),
            array(
                'title' => 'Business Associates',
                'url' => '/businessassociate/list',
            ),
            array(
                'title' => 'Breach Management',
                'url' => '/breachlog/list',
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                        ),
                    array('title' => 'Breach Remediation Plans',
                         'url' => '/breachremediationplan/list',
                        )
                )
            ),
            array(
                'title' => 'Assessments',
                'url' => '/assessment/list',
            ),
            array(
                'title' => 'Remediation Plans',
                'url' => '/remediationplan/list',
                'items' => array(
                )
            ),
            array(
                'title' => 'Trainings',
                'url' => '/traininglog/list',
                'items' => array(
                    array('title' => 'Training Logs',
                            'url' => '/traininglog/list'
                        ),
                    array('title' => 'Security Reminder',
                         'url' => '/securityreminder/list',
                        )
                )
            ),
            array(
                'title' => 'Users',
                'url' => '/admin/users',
                // 'items' => array(
                //     array('title' => 'Add new user', 'url' => '/admin/adduser'),
                // )
            ),
        );
    }

    private function _prepareItemsForConsultant()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/consultant',
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list'
            ),
            array(
                'title' => 'Business Associates',
                'url' => '/businessassociate/list',
            ),
            array(
                'title' => 'Breach Management',
                'url' => '/breachlog/list',
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                        ),
                    array('title' => 'Breach Remediation Plans',
                         'url' => '/breachremediationplan/list',
                        )
                )
            ),
            array(
                'title' => 'Assessments',
                'url' => '/assessment/list',
            ),
            array(
                'title' => 'Remediation Plans',
                'url' => '/remediationplan/list',
            ),
            array(
                'title' => 'Trainings',
                'url' => '/traininglog/list',
                'items' => array(
                    array('title' => 'Training Logs',
                            'url' => '/traininglog/list'
                        ),
                    array('title' => 'Security Reminder',
                         'url' => '/securityreminder/list',
                        )
                )
            )
        );
    }

    private function _prepareItemsForClient()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/client',
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list'
            ),
            array(
                'title' => 'Business Associates',
                'url' => '/businessassociate/list',
            ),
            array(
                'title' => 'Breach Management',
                'url' => '/breachlog/list',
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                        ),
                    array('title' => 'Breach Remediation Plans',
                          'url' => '/breachremediationplan/list',
                        )
                )
            ),
            array(
                'title' => 'Assessments',
                'url' => '/assessment/list',
            ),
            array(
                'title' => 'Remediation Plans',
                'url' => '/remediationplan/list',
            ),
            array(
                'title' => 'Trainings',
                'url' => '/traininglog/list',
                'items' => array(
                    array('title' => 'Training Logs',
                          'url' => '/traininglog/list'
                        ),
                    array('title' => 'Security Reminder',
                          'url' => '/securityreminder/list',
                        )
                )
            )
        );
    }

    private function _prepareItemsForPartial()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/client'
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list'
            ),
            array(
                'title' => 'Business Associates',
                'url' => '/businessassociate/list',
                'disabled' => true
            ),
            array(
                'title' => 'Breach Management',
                'url' => '/breachlog/list',
                'disabled' => true,
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                          'disabled' => true
                        ),
                    array('title' => 'Breach Remediation Plans',
                          'url' => '/breachremediationplan/list',
                          'disabled' => true
                        )
                )
            ),
            array(
                'title' => 'Assessments',
                'url' => '/assessment/list',
                'disabled' => true
            ),
            array(
                'title' => 'Remediation Plans',
                'url' => '/remediationplan/list',
                'disabled' => true
            ),
            array(
                'title' => 'Trainings',
                'url' => '/traininglog/list',
                'items' => array(
                    array('title' => 'Training Logs',
                            'url' => '/traininglog/list'
                        ),
                    array('title' => 'Security Reminder',
                         'url' => '/securityreminder/list',
                        )
                )
            )
        );
    }

    private function _prepareItemsForSalesRep()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/salesrep',
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list'
            ),
        );
    }

    private function _prepareItemsForCoordinator()
    {
        $this->items = array(
            array(
                'title' => 'Powiadomienia',
                'items' => array(
                    array('title' => 'Wyślij powiadomienie', 'url' => '/notifications/create'),
                )
            ),
            array(
                'title' => 'Dobre praktyki',
                'items' => array(
                    array('title' => 'Wszystkie dobre praktyki', 'url' => '/practice/alllist'),
                    array('title' => 'Aktywne dobre praktyki', 'url' => '/practice/activelist'),
                    array('title' => 'Czekające na akceptację', 'url' => '/practice/toacceptlist'),
                    array('title' => 'border', 'url' => ''),
                    array('title' => 'Edycja formularzy', 'url' => '/practice/formedit'),
                    array('title' => 'Dodaj nowy projekt', 'url' => '/practice/create'),
                    array('title' => 'border', 'url' => ''),
                    array('title' => 'Lista przedstawicieli', 'url' => '/user/representativelist'),
                    array('title' => 'Przedstawiciele do akceptacji', 'url' => '/user/representativetoacceptlist'),
                    array('title' => 'Lista innych dodających', 'url' => '/user/simpleuserlist'),
                )
            ),
            array(
                'title' => 'Konkursy',
                'items' => array(
                    array('title' => 'Wszystkie konkursy', 'url' => '/contest/list'),
                    array('title' => 'Aktywne konkursy', 'url' => '/contest/activelist'),
                    array('title' => 'Zgłoszenia do konkursów', 'url' => '/contest/userlist'),
                    array('title' => 'border', 'url' => ''),
                    array('title' => 'Lista jurorów', 'url' => '/contest/jurors'),
                    array('title' => 'Dodaj konkurs', 'url' => '/contest/create'),
                )
            ),
            array(
                'title' => 'Komentarze',
                'items' => array(
                    array('title' => 'Do zatwierdzenia', 'url' => '/practice/commentlist'),
                )
            )
        );
    }

    private function _setActive($sl)
    {
        $router = $sl->get('router');
        $request = $sl->get('request');
        $routeMatch = $router->match($request);

        if (!is_object($routeMatch)) {
            return false;
        }

        $controller = $routeMatch->getMatchedRouteName();
        $action = $routeMatch->getParam('action');

        foreach ($this->items as &$item) {
            if ($item['url'] == '/' . $controller . '/' . $action) {
                $item['active'] = true;
            }
        }
    }

    private function _prepareItemsForRepresentativeUser()
    {
        $this->items[] = array('name' => 'O bazie', 'url' => '/o-bazie');
        $this->items[] = array('name' => 'Kontakt', 'url' => '/contact');
    }

    private function _checkIfAllowRenderMenu($sl)
    {
        $this->sl = $sl;

        $router = $this->sl->get('router');
        $request = $this->sl->get('request');
        $routeMatch = $router->match($request);

        if (!is_object($routeMatch)) {
            return false;
        }
        //$controller = $routeMatch->getParam('controller');

        $controller = $routeMatch->getMatchedRouteName();
        $action = $routeMatch->getParam('action');

        $actions = array(
            'aboutbase' => array(
                'aboutbase' => true,
            ),
            'contact' => array(
                'contact' => true
            )

        );

        return isset($actions[$controller][$action]) ? true : false;
    }
}