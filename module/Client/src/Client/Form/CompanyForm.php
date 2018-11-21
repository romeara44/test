<?php
namespace Client\Form;

use Zend\Form\Form;

class CompanyForm extends Form
{
    public function __construct($sl, $c_id = 0)
    {
        parent::__construct('user');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'c_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $companyTable = $sl->get('Client\Model\CompanyTable');

        $parent_companies[0] = 'Please select';
        foreach ($companyTable->getCompaniesForParent($identity) as $key => $c) {
            $parent_companies[$key] = $c;
        }
        if ($c_id) {
            unset($parent_companies[$c_id]);
        }

        $this->add(array(
            'name' => 'c_parent_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Parent',
                'value_options' => $parent_companies
            ),
        ));

        $companyTable = $sl->get('Client\Model\CompanyTable');
        $child_companies[0] = 'Please select';
        foreach ($companyTable->getCompaniesForChild($identity) as $key => $c) {
            $child_companies[$key] = $c;
        }
        if ($c_id) {
            unset($child_companies[$c_id]);
        }
        $this->add(array(
            'name' => '_c_child_c_ids',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Childs',
                'value_options' => $child_companies
            ),
        ));

        $c_rel_types[0] = 'Please select';
        $c_rel_types[\Client\Model\Company::RELATION_TYPE_PARENT] = 'Parent';
        $c_rel_types[\Client\Model\Company::RELATION_TYPE_CHILD] = 'Child';

        $this->add(array(
            'name' => 'c_rel_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Relation',
                'value_options' => $c_rel_types
            ),
        ));

        $c_parent_types[0] = 'Please select';
        $c_parent_types[\Client\Model\Company::PARENT_TYPE_OPERATION] = 'Operating Company Parent';
        $c_parent_types[\Client\Model\Company::PARENT_TYPE_HOLDING] = 'Holding Company Parent';

        $this->add(array(
            'name' => 'c_parent_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => $c_parent_types
            ),
        ));

        $c_child_types[0] = 'Please select';
        $c_child_types[\Client\Model\Company::CHILD_TYPE_AS_COMPANY] = 'treat as a Company';
        $c_child_types[\Client\Model\Company::CHILD_TYPE_LOCATION_ONLY] = 'Location Only';

        $this->add(array(
            'name' => 'c_child_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => $c_child_types
            ),
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $consultants[''] = 'Please select';
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_SENIOR_CONSULTANT, \Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SALES_REP)) as $key => $r) {
            $consultants[$key] = $r;
        }

        $types[''] = 'Please select';
        $types = ['county', 'private office', 'hospital']; // This is hardcoded, will have to build a DB model for new table
        foreach ($types as $index => $type) {
            $types[$index] = $type;
        }

        $this->add(array(
            'name' => '_c_cur_consultants',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Company Type',
                'value_options' => $consultants
            ),
        ));

        $this->add(array(
            'name' => '_company_types',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Consultants',
                'value_options' => $types
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
            'name' => 'c_renewal_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Renewal Date',
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

        $this->add(array(
            'name' => 'adr_name[]',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

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