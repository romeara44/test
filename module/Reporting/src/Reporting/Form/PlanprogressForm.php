<?php
namespace Reporting\Form;

use Zend\Form\Form;

class PlanprogressForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $companyTable = $sl->get('Client\Model\CompanyTable');
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        if(count($companies) != 1) {
            $companies = array('' => 'All') + $companies;
        }

        $this->add(array(
            'name' => 'company',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => array( ''        => 'All',
                                         'security' => 'Security Risk Assessments',
                                         'privacy'  => 'Privacy Risk Assessments',
                                         'imported' => 'Imported Remediation Plans',
                                         'breach'   => 'Breach Remediation Plans',
                                        )
            ),
        ));

        $statuses = array('' => 'All') + \Assessment\Model\Remediationplan::$statusesNames;
        unset($statuses[\Assessment\Model\Remediationplan::STATUS_CLOSED]);

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => $statuses
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Search',
                'id' => 'submitbutton',
            ),
        ));
    }
}