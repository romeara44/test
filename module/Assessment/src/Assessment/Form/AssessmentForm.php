<?php
namespace Assessment\Form;

use Zend\Form\Form;

class AssessmentForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'a_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $companyTable = $sl->get('Client\Model\CompanyTable');
        $companies[''] = 'Select Client';
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'a_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Client Name',
                'value_options' => $companies
            ),
        ));


        $this->add(array(
            'name' => 'a_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => ' Assessment Type',
                'value_options' => array('' => 'Select Assessment Type', 0 => '---', \Assessment\Model\Assessment::$typesNames[\Assessment\Model\Assessment::TYPE_SECURITY_RISK], \Assessment\Model\Assessment::$typesNames[\Assessment\Model\Assessment::TYPE_PRIVACY_RISK])
            ),
        ));

        ////////////////////////////////////

        $this->add(array(
            'name' => 'adr_address1[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 1',
            ),
        ));

        $this->add(array(
            'name' => 'adr_address2[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 2',
            ),
        ));

        $this->add(array(
            'name' => 'adr_city[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'City',
            ),
        ));

        $stateTable = $sl->get('Admin\Model\StateTable');
        $states[''] = 'Please Select';
        $states[0] = '---';
        foreach ($stateTable->getStates() as $key => $r) {
            $states[$key] = $r;
        }

        $this->add(array(
            'name' => 'adr_state_id[]',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'State',
                'value_options' => $states
            ),
        ));

        $this->add(array(
            'name' => 'adr_zip[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'ZIP Code',
            ),
        ));

        $this->add(array(
            'name' => 'adr_name[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Location name',
            ),
        ));

        $this->add(array(
            'name' => 'adr_phone[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Phone',
            ),
        ));

        $this->add(array(
            'name' => 'adr_phone_inner[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'adr_other_phone[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'adr_other_phone_inner[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'adr_fax[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Fax',
            ),
        ));

        $this->add(array(
            'name' => 'adr_email[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Email',
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