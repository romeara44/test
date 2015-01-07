<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Company
{
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
    public $c_consultant_u_id;
    public $c_owner_u_id;
    public $c_update_u_id;

    public $c_create_date;
    public $c_update_date;

    public $u_id;
    public $u_firstname;
    public $u_lastname;
    public $u_office_phone;
    public $_c_active;

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
        $this->u_id     = (isset($data['u_id'])) ? $data['u_id'] : null;
        $this->u_firstname     = (isset($data['u_firstname'])) ? $data['u_firstname'] : null;
        $this->u_lastname     = (isset($data['u_lastname'])) ? $data['u_lastname'] : null;
        $this->u_office_phone     = (isset($data['u_office_phone'])) ? $data['u_office_phone'] : null;
        $this->c_consultant_u_id     = (isset($data['c_consultant_u_id'])) ? $data['c_consultant_u_id'] : null;
        $this->c_owner_u_id     = (isset($data['c_owner_u_id'])) ? $data['c_owner_u_id'] : null;
        $this->c_update_u_id     = (isset($data['c_update_u_id'])) ? $data['c_update_u_id'] : null;
        $this->_c_active     = (isset($data['_c_active'])) ? $data['_c_active'] : null;
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
                'name'     => 'c_consultant_u_id',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}