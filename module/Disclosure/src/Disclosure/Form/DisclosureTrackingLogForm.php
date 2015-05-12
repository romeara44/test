<?php
namespace Disclosure\Form;

use Zend\Form\Form;

class DisclosureTrackingLogForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->add(array(
            'name' => 'dtl_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_reference_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Reference Number',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_patient_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Name',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_medical_record_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Medical Record Number',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_date_received',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Received',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_name_of_requestor',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name Of Requestor',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_auth_type',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Auth Type',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_purpose_of_disclosure',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Purpose Of Disclosure',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_phi_information_disclosed',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Purpose Of Disclosure',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_date_disclosed',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Disclosed',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_disclosed_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Disclosed By',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_extension_notification',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Extension Notification',
            ),
        ));

        $this->add(array(
            'name' => 'dtl_copy_of_request',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Copy Of Request',
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