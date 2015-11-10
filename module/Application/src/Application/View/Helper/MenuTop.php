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

        $model = new ViewModel(array('items' => $this->items, 'checkClientLimitCompany' => $sl->get('Client\Model\CompanyTable')->checkClientLimitCompany()));
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
            if($identity['u_company_id_admin']) {
                $this->_prepareItemsForClientCompanyAdmin($identity);
            } else {
                $this->_prepareItemsForClient($identity);
            }
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
                'url' => '/dashboard/admin'
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'Business Associates',
                          'url' => '/businessassociate/list',
                    ),
                    array('title' => 'All Clients',
                          'url' => '/client/list',
                        ),
                    array('title' => 'Create Contact',
                          'url' => '/client/edit',
                        ),
                    array('title' => 'Create Company',
                          'url' => '/company/edit',
                        )
                )
            ),
            array(
                'title' => 'Incident Response',
                'url' => '/breachlog/list',
                'class' => 'with-access',
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                         'class' => 'with-access'
                        ),
                    array('title' => 'Breach Remediation Plans',
                         'url' => '/breachremediationplan/list',
                         'class' => 'with-access'
                        )
                )
            ),
            array(
                'title' => 'Assessments',
                'url' => '/assessment/list',
            ),
            array(
                'title' => 'Remediation Plans',
                'url' => '/remediationplan/list'
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
                'title' => 'Disclosures',
                'url' => '/disclosurerequest/list',
                'class' => 'with-access',
                'items' => array(
                    array('title' => 'Requests',
                            'url' => '/disclosurerequest/list',
                            'class' => 'with-access'
                        ),
                    array('title' => 'Disclosure Tracking Logs',
                         'url' => '/disclosuretrackinglog/list',
                         'class' => 'with-access',
                        ),
                    array('title' => 'Verbal Logs',
                         'url' => '/verballog/list',
                         'class' => 'with-access',
                        )
                )
            ),
            array(
                'title' => 'Reporting',
                'url' => '/reporting/auditbreach',
                'items' => array(
                    array('title' => 'Audit/Breach',
                            'url' => '/reporting/auditbreach'
                        ),
                    array('title' => 'Plans Progress',
                         'url' => '/reporting/planprogress',
                        )
                )
            ),
            array(
                'title' => 'Users',
                'url' => '/admin/users'
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
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'Business Associates',
                          'url' => '/businessassociate/list',
                    ),
                    array('title' => 'All Clients',
                          'url' => '/client/list',
                        ),
                    array('title' => 'Create Contact',
                          'url' => '/client/edit',
                        ),
                    array('title' => 'Create Company',
                          'url' => '/company/edit',
                        )
                )
            ),
            array(
                'title' => 'Incident Response',
                'url' => '/breachlog/list',
                'class' => 'with-access',
                'items' => array(
                    array('title' => 'Breach Logs',
                          'url' => '/breachlog/list',
                          'class' => 'with-access'
                        ),
                    array('title' => 'Breach Remediation Plans',
                         'url' => '/breachremediationplan/list',
                         'class' => 'with-access'
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
            ),
            array(
                'title' => 'Disclosures',
                'url' => '/disclosurerequest/list',
                'class' => 'with-access',
                'items' => array(
                    array('title' => 'Requests',
                            'url' => '/disclosurerequest/list',
                            'class' => 'with-access'
                        ),
                    array('title' => 'Disclosure Tracking Logs',
                         'url' => '/disclosuretrackinglog/list',
                         'class' => 'with-access'
                        ),
                    array('title' => 'Verbal Logs',
                         'url' => '/verballog/list',
                         'class' => 'with-access'
                        )
                )
            ),
            array(
                'title' => 'Reporting',
                'url' => '/reporting/auditbreach',
                'items' => array(
                    array('title' => 'Audit/Breach',
                            'url' => '/reporting/auditbreach'
                        ),
                    array('title' => 'Plans Progress',
                         'url' => '/reporting/planprogress',
                        )
                )
            ),
            array(
                'title' => 'Users',
                'url' => '/admin/users'
            )
        );
    }

    private function _prepareItemsForClient($identity)
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/client',
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'Business Associates',
                          'url' => '/businessassociate/list',
                    )
                )
            )
        );

        if($identity['u_grant_to_breach']) {
            $this->items[] = array(
                                'title' => 'Incident Response',
                                'url' => '/breachlog/list',
                                'class' => 'with-access',
                                'items' => array(
                                    array('title' => 'Breach Logs',
                                          'url' => '/breachlog/list',
                                          'class' => 'with-access'
                                        ),
                                    array('title' => 'Breach Remediation Plans',
                                          'url' => '/breachremediationplan/list',
                                          'class' => 'with-access'
                                        )
                                )
                            );
        }

        $this->items = array_merge($this->items, array(
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
                                                        ),
                                                        array(
                                                            'title' => 'Reporting',
                                                            'url' => '/reporting/auditbreach',
                                                            'items' => array(
                                                                array('title' => 'Audit/Breach',
                                                                        'url' => '/reporting/auditbreach'
                                                                    ),
                                                                array('title' => 'Plans Progress',
                                                                     'url' => '/reporting/planprogress',
                                                                    )
                                                            )
                                                        )
                                                    )
                                                );

        if($identity['u_grant_to_disclosures']) {
            $this->items[] = array(
                                'title' => 'Disclosures',
                                'url' => '/disclosurerequest/list',
                                'class' => 'with-access',
                                'items' => array(
                                    array('title' => 'Requests',
                                            'url' => '/disclosurerequest/list',
                                            'class' => 'with-access'
                                        ),
                                    array('title' => 'Disclosure Tracking Logs',
                                         'url' => '/disclosuretrackinglog/list',
                                         'class' => 'with-access'
                                        ),
                                    array('title' => 'Verbal Logs',
                                         'url' => '/verballog/list',
                                         'class' => 'with-access'
                                        )
                                )
                            );
        }
    }

    private function _prepareItemsForClientCompanyAdmin($identity)
    {
        $this->items = array(
            array(
                'title' => 'Dashboard',
                'url' => '/dashboard/client',
            ),
            array(
                'title' => 'Clients',
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'Business Associates',
                          'url' => '/businessassociate/list',
                    ),
                    array('title' => 'All Clients',
                          'url' => '/client/list',
                        ),
                    array('title' => 'Create Contact',
                          'url' => '/client/edit',
                        ),
                    array('title' => 'Create Company',
                          'url' => '/company/edit',
                        )
                )
            )
        );

            if($identity['u_grant_to_breach']) {
                $this->items[] = array(
                                    'title' => 'Incident Response',
                                    'url' => '/breachlog/list',
                                    'class' => 'with-access',
                                    'items' => array(
                                        array('title' => 'Breach Logs',
                                              'url' => '/breachlog/list',
                                              'class' => 'with-access'
                                            ),
                                        array('title' => 'Breach Remediation Plans',
                                              'url' => '/breachremediationplan/list',
                                              'class' => 'with-access'
                                            )
                                    )
                                );
            }
            
            $this->items = array_merge($this->items, array(
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
                                                        ),
                                                        array(
                                                            'title' => 'Reporting',
                                                            'url' => '/reporting/auditbreach',
                                                            'items' => array(
                                                                array('title' => 'Audit/Breach',
                                                                        'url' => '/reporting/auditbreach'
                                                                    ),
                                                                array('title' => 'Plans Progress',
                                                                     'url' => '/reporting/planprogress',
                                                                    )
                                                            )
                                                        )
                                                    )
                                                );


        if($identity['u_grant_to_disclosures']) {
            $this->items[] = array(
                                'title' => 'Disclosures',
                                'url' => '/disclosurerequest/list',
                                'class' => 'with-access',
                                'items' => array(
                                    array('title' => 'Requests',
                                            'url' => '/disclosurerequest/list',
                                            'class' => 'with-access'
                                        ),
                                    array('title' => 'Disclosure Tracking Logs',
                                         'url' => '/disclosuretrackinglog/list',
                                         'class' => 'with-access'
                                        ),
                                    array('title' => 'Verbal Logs',
                                         'url' => '/verballog/list',
                                         'class' => 'with-access'
                                        )
                                )
                            );
        }
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
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'Business Associates',
                          'url' => '/businessassociate/list',
                          'disabled' => true
                         ),
                    array('title' => 'All Clients',
                          'url' => '/client/list',
                        ),
                    array('title' => 'Create Contact',
                          'url' => '/client/edit',
                        ),
                    array('title' => 'Create Company',
                          'url' => '/company/edit',
                        )
                )
            ),
            array(
                'title' => 'Incident Response',
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
                'url' => '/client/list',
                'items' => array(
                    array('title' => 'All Clients',
                          'url' => '/client/list',
                        ),
                    array('title' => 'Create Contact',
                          'url' => '/client/edit',
                        ),
                    array('title' => 'Create Company',
                          'url' => '/company/edit',
                        )
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