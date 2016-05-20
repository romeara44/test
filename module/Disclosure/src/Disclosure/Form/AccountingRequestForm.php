<?php
namespace Disclosure\Form;

use Zend\Form\Form;

class AccountingRequestForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->add(array(
            'name' => 'ar_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'ar_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'ar_location',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Office/Location',
            ),
        ));

        $this->add(array(
            'name' => 'ar_requested_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Requested By; (Name of Individual/ Legal Rep):',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_requested',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Requested',
            ),
        ));

        $this->add(array(
            'name' => 'ar_disclosure_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address to Send Disclosure Accounting',
            ),
        ));

        $this->add(array(
            'name' => 'ar_patient_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Name',
            ),
        ));

        $this->add(array(
            'name' => 'ar_medical_record_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Medical Record Number',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_of_birth',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Date of Birth',
            ),
        ));

        $this->add(array(
            'name' => 'ar_patient_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Address',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_requested_from',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'From',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_requested_to',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'To',
            ),
        ));

        $this->add(array(
            'name' => 'ar_fees_charge',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Charge ($)',
            ),
        ));

        $this->add(array(
            'name' => 'ar_extension_reason',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Give reason',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_sent',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Accounting of Disclosures Sent',
            ),
        ));

        $this->add(array(
            'name' => 'ar_date_patient_notified',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Patient Notified',
            ),
        ));

        $this->add(array(
            'name' => 'ar_staff_member',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Staff Member Completing Request',
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