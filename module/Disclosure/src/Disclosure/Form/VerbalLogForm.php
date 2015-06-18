<?php
namespace Disclosure\Form;

use Zend\Form\Form;

class VerbalLogForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->add(array(
            'name' => 'vl_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'vl_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_of_request',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date of request',
            ),
        ));

        $this->add(array(
            'name' => 'vl_medical_record_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Medical record number',
            ),
        ));

        $this->add(array(
            'name' => 'vl_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_of_birth',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date of birth',
            ),
        ));
  
        $this->add(array(
            'name' => 'vl_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address',
            ),
        ));

        $this->add(array(
            'name' => 'vl_disclosure_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Disclosure Accounting Address',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_requested_from',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'From',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_requested_to',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'To',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_request_received',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Request Received',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_account_sent',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Accounting Sent',
            ),
        ));

        $this->add(array(
            'name' => 'vl_date_patient_notified',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Patient Notified',
            ),
        ));

        $this->add(array(
            'name' => 'vl_staff_member',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Staff Member',
            ),
        ));

        $this->add(array(
            'name' => 'vl_fees_charge',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Charge ($)',
            ),
        ));

        $this->add(array(
            'name' => 'vl_extension_reason',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Give reason',
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