<?php
namespace Disclosure\Form;

use Zend\Form\Form;

class DisclosureRequestForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->add(array(
            'name' => 'dr_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'dr_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'dr_reference_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Reference Number',
            ),
        ));

        $this->add(array(
            'name' => 'dr_requested_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Requested By',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_requested',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Requested',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_range_requested',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Range Requested',
            ),
        ));

        $this->add(array(
            'name' => 'dr_staff_member',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Staff Member',
            ),
        ));

        $this->add(array(
            'name' => 'dr_completing_request',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Completing Request',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_provided',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Provided',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
}