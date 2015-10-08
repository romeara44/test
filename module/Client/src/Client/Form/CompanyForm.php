<?php
namespace Client\Form;

use Zend\Form\Form;

class CompanyForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'c_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $consultants[''] = 'Please select';
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_SENIOR_CONSULTANT, \Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SALES_REP)) as $key => $r) {
            $consultants[$key] = $r;
        }

        $this->add(array(
            'name' => '_c_cur_consultants',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Consultants',
                'value_options' => $consultants
            ),
        ));

        $this->add(array(
            'name' => 'c_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Company name',
            )
        ));


        $this->add(array(
            'name' => 'c_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail',
            ),
        ));

        $this->add(array(
            'name' => 'c_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Phone',
            ),
        ));

        $this->add(array(
            'name' => 'c_other_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'c_other_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'c_fax',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Fax',
            ),
        ));


        $this->add(array(
            'name' => 'c_website',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Website',
            ),
        ));


        $this->add(array(
            'name' => 'c_users_limit',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Max. number of users',
                'value_options' => $sl->get('Client\Model\CompanyTable')->getUsersLimitsArray()
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
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
}