<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Company
{
    const RELATION_TYPE_PARENT = 1;
    const RELATION_TYPE_CHILD = 2;

    const PARENT_TYPE_OPERATION = 1;
    const PARENT_TYPE_HOLDING = 2;

    const CHILD_TYPE_AS_COMPANY = 1;
    const CHILD_TYPE_LOCATION_ONLY = 2;

    public $c_id;
    public $c_name;
    public $c_email;
    public $c_phone;
    public $c_other_phone;
    public $c_other_phone_inner;
    public $c_fax;
    public $c_website;
    public $c_primary_adr_id;
    public $c_active;
    public $c_primary_contact_u_id;
    public $c_training_manager_u_id;
    public $c_consultant_u_id;
    public $c_owner_u_id;
    public $c_users_limit;
    public $c_update_u_id;

    public $c_create_date;
    public $c_update_date;

    public $u_id;
    public $u_firstname;
    public $u_lastname;
    public $u_office_phone;
    public $_c_active;
    public $_c_consultant_id;
    public $_c_cur_consultants;
    
    public $c_rel_type;
    public $c_type;
    public $c_parent_type;
    public $c_child_type;
    public $c_parent_c_id;
    public $_c_child_c_ids;

    public $c_renewal_date;
    public $c_renewal_email_recipients;
    public $c_deny_renewal_email_sending;
    public $company_type_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->c_id     = (isset($data['c_id'])) ? $data['c_id'] : null;
        $this->c_name     = (isset($data['c_name'])) ? $data['c_name'] : null;
        $this->c_email     = (isset($data['c_email'])) ? $data['c_email'] : null;
        $this->c_phone     = (isset($data['c_phone'])) ? $data['c_phone'] : null;
        $this->c_other_phone     = (isset($data['c_other_phone'])) ? $data['c_other_phone'] : null;
        $this->c_other_phone_inner     = (isset($data['c_other_phone_inner'])) ? $data['c_other_phone_inner'] : null;
        $this->c_fax     = (isset($data['c_fax'])) ? $data['c_fax'] : null;
        $this->c_website     = (isset($data['c_website'])) ? $data['c_website'] : null;
        $this->c_primary_adr_id     = (isset($data['c_primary_adr_id'])) ? $data['c_primary_adr_id'] : null;
        $this->c_primary_contact_u_id     = (isset($data['c_primary_contact_u_id'])) ? $data['c_primary_contact_u_id'] : null;
        $this->c_training_manager_u_id     = (isset($data['c_training_manager_u_id'])) ? $data['c_training_manager_u_id'] : null;
        $this->u_id     = (isset($data['u_id'])) ? $data['u_id'] : null;
        $this->u_firstname     = (isset($data['u_firstname'])) ? $data['u_firstname'] : null;
        $this->u_lastname     = (isset($data['u_lastname'])) ? $data['u_lastname'] : null;
        $this->u_office_phone     = (isset($data['u_office_phone'])) ? $data['u_office_phone'] : null;
        $this->c_consultant_u_id     = (isset($data['c_consultant_u_id'])) ? $data['c_consultant_u_id'] : null;
        $this->c_owner_u_id     = (isset($data['c_owner_u_id'])) ? $data['c_owner_u_id'] : null;
        $this->c_users_limit     = (isset($data['c_users_limit'])) ? $data['c_users_limit'] : 0;
        $this->c_update_u_id     = (isset($data['c_update_u_id'])) ? $data['c_update_u_id'] : null;
        $this->_c_active     = (isset($data['_c_active'])) ? $data['_c_active'] : null;
        $this->_c_consultant_id     = (isset($data['_c_consultant_id'])) ? $data['_c_consultant_id'] : null;
        $this->_c_cur_consultants     = (isset($data['_c_cur_consultants'])) ? $data['_c_cur_consultants'] : null;
        $this->c_parent_c_id     = (isset($data['c_parent_c_id'])) ? $data['c_parent_c_id'] : null;
        $this->c_rel_type     = (isset($data['c_rel_type'])) ? $data['c_rel_type'] : null;
        $this->c_type     = (isset($data['c_type'])) ? $data['c_type'] : null;
        $this->c_parent_type     = (isset($data['c_parent_type'])) ? $data['c_parent_type'] : null;
        $this->c_child_type     = (isset($data['c_child_type'])) ? $data['c_child_type'] : null;
        $this->_c_child_c_ids     = (isset($data['_c_child_c_ids'])) ? $data['_c_child_c_ids'] : null;

        $this->c_create_date     = (isset($data['c_create_date'])) ? $data['c_create_date'] : null;
        $this->c_renewal_date     = (isset($data['c_renewal_date'])) ? $data['c_renewal_date'] : null;
        $this->c_renewal_email_recipients     = (isset($data['c_renewal_email_recipients'])) ? $data['c_renewal_email_recipients'] : null;
        $this->c_deny_renewal_email_sending     = (isset($data['c_deny_renewal_email_sending'])) ? $data['c_deny_renewal_email_sending'] : null;
        $this->company_type_id     = (isset($data['company_type_id'])) ? $data['company_type_id'] : 1;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl, $isEdit = false)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_users_limit',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'adr_state_id[]',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_primary_contact_u_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => '_c_cur_consultants',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_rel_type',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_parent_type',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_child_type',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'c_parent_c_id',
                'required' => false,
                'validators' => array(
                    array(
                        'name'    => '\Client\Validator\ParentCompany',
                        'options' => array(
                            'model'      => $this,
                            'sl' => $sl,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => '_c_child_c_ids',
                'required' => false,
                'validators' => array(
                    array(
                        'name'    => '\Client\Validator\ChildCompanies',
                        'options' => array(
                            'model'      => $this,
                            'sl' => $sl,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}