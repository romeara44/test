<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class MenuLeft extends AbstractHelper
{
    private $sm;
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

    public function __invoke($sm)
    {
        if ($this->_checkIfAllowRenderMenu($sm)) {
           //return '';
        }

        if (!$this->hasIdentity()) {
            return '';
        }
        $this->_prepareItems();
        $this->_setActive($sm);

        $model = new ViewModel(array('items' => $this->items));
        $model->setTemplate('application/partials/menuleft');

        return $this->getView()->render($model);
    }

    private function _prepareItems()
    {
        $identity = $this->getIdentity();

        if ($identity['u_role_id'] == \User\Model\User::ROLE_ADMIN) { // items for logged in
            $this->_prepareItemsForAdmin();
        } elseif ($identity['u_role_id'] == \User\Model\User::ROLE_SENIOR_CONSULTANT) {
            $this->_prepareItemsForSimpleUser();
        } elseif ($identity['u_role_id'] == \User\Model\User::ROLE_CONSULTANT) {
            $this->_prepareItemsForCoordinator();
        }
    }

    private function _prepareItemsForAdmin()
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'items' => array(
                    array('title' => 'Add new user', 'url' => '/users/edit'),
                )
            ),
            array(
                'title' => 'Users',
                'items' => array(
                    array('title' => 'Add new user', 'url' => '/users/edit'),
                )
            ),
            array(
                'title' => 'Remediation Plans',
                'items' => array()
            )
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

    private function _setActive($sm)
    {
        $router = $sm->get('router');
        $request = $sm->get('request');
        $routeMatch = $router->match($request);

        if (!is_object($routeMatch)) {
            return false;
        }

        $controller = $routeMatch->getMatchedRouteName();
        $action = $routeMatch->getParam('action');

        foreach ($this->items as &$item) {
            foreach ($item['items'] as &$subitem) {
                if ($subitem['url'] == '/' . $controller . '/' . $action) {
                    $subitem['active'] = true;
                }
            }
        }
    }

    private function _prepareItemsForRepresentativeUser()
    {
        $this->items[] = array('name' => 'O bazie', 'url' => '/o-bazie');
        $this->items[] = array('name' => 'Kontakt', 'url' => '/contact');
    }

    private function _checkIfAllowRenderMenu($sm)
    {
        $this->sm = $sm;

        $router = $this->sm->get('router');
        $request = $this->sm->get('request');
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