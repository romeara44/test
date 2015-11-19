<?php
namespace Admin\Form;

use Zend\Form\Form;
use Admin\Model\RoleTable;

class UserForm extends Form
{
    public function __construct($sl, $uRoleId = null, $identityUid = null)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'u_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $roleTable = $sl->get('Admin\Model\RoleTable');
        $roles[''] = 'Please Select';
        $roles[0] = '---';
        foreach ($roleTable->getRoles($uRoleId, $identityUid) as $key => $r) {
            $roles[$key] = $r;
        }

        $this->add(array(
            'name' => 'u_role_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Account Type',
                'value_options' => $roles
            ),
        ));

        $companies = array();
        
        $companyTable = $sl->get('Client\Model\CompanyTable');
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        if(count($companies) != 1) {
            $companies = array('' => 'Please select') + $companies;
        }

        $this->add(array(
            'name' => 'u_company_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $seniorConsultants[''] = 'Please Select';
        $seniorConsultants[0] = '---';
        foreach ($userTable->getSeniorConsultants() as $key => $r) {
            $seniorConsultants[$key] = $r;
        }

        $this->add(array(
            'name' => 'u_senior_consultant_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Senior Consultant',
                'value_options' => $seniorConsultants
            ),
        ));


        $this->add(array(
            'name' => 'u_firstname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'First name',
            )
        ));
        $this->add(array(
            'name' => 'u_lastname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Last Name',
            ),
        ));

        $this->add(array(
            'name' => 'u_title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));

        $this->add(array(
            'name' => 'u_office_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Office Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_office_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_direct_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Direct Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_direct_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_cell_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Cell Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_fax',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Fax',
            ),
        ));

        $this->add(array(
            'name' => 'u_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail',
            ),
        ));

        $this->add(array(
            'name' => 'u_address1',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 1',
            ),
        ));

        $this->add(array(
            'name' => 'u_address2',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 2',
            ),
        ));

        $this->add(array(
            'name' => 'u_city',
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
            'name' => 'u_state_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'State',
                'value_options' => $states
            ),
        ));

        $this->add(array(
            'name' => 'u_zip',
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