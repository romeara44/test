<?php
namespace Disclosure\Form;

use Zend\Form\Form;

class DisclosureRecordForm extends Form
{
    public function __construct($sl, $drObj = null)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $addresses = array();
        if ($drObj && $drObj->dr_c_id) {
            $addresses = $sl->get('Client\Model\AddressTable')->getAddresses($drObj->dr_c_id, \Client\Model\AddressItem::COMPANY_TYPE);
        }

        $res = array('0' => 'Select location');
        foreach ($addresses as $value) {
            $res[$value->adr_id] = $value->adr_name;
        }
        
        $this->add(array(
            'name' => 'dr_adr_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Location',
                'value_options' => $res,
            ),
        ));

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

        $companyTable = $sl->get('Client\Model\CompanyTable');
        $companies[''] = 'Select Company';
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'dr_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_received',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Received',
            ),
        ));

        $this->add(array(
            'name' => 'dr_patient_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Name',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_disclosure',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Disclosure Date',
            ),
        ));

        $this->add(array(
            'name' => 'dr_medical_record_number',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Medical Record Number',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_of_birth',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Patient Date of birth',
            ),
        ));

        $this->add(array(
            'name' => 'dr_phi_information_disclosed',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Description of PHI Disclosed',
            ),
        ));

        $this->add(array(
            'name' => 'dr_purpose_of_disclosure',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Purpose for Disclosure',
            ),
        ));

        $this->add(array(
            'name' => 'dr_name_of_requestor',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name of Entity
                    of Person who
                    received the PHI',
            ),
        ));

        $this->add(array(
            'name' => 'dr_address',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address of Entity
                    of Person who
                    received the PHI',
            ),
        ));

        $this->add(array(
            'name' => 'dr_entered_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_entered',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date',
            ),
        ));

        $this->add(array(
            'name' => 'dr_reviewed_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_reviewed',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date',
            ),
        ));

        $this->add(array(
            'name' => 'dr_disclosed_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'dr_date_disclosed',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date',
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