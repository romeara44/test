<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Address
{
    public $adr_id;
    public $adr_address1;
    public $adr_address2;
    public $adr_city;
    public $adr_state_id;
    public $adr_zip;

    public $adr_name;
    public $adr_phone;
    public $adr_phone_inner;
    public $adr_other_phone;
    public $adr_other_phone_inner;
    public $adr_fax;
    public $adr_email;

    public $adr_active;

    public $adr_create_date;
    public $adr_update_date;

    public $_state_name;
    public $_state_code;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->adr_id     = (isset($data['adr_id'])) ? $data['adr_id'] : null;
        $this->adr_address1     = (isset($data['adr_address1'])) ? $data['adr_address1'] : null;
        $this->adr_address2     = (isset($data['adr_address2'])) ? $data['adr_address2'] : null;
        $this->adr_city     = (isset($data['adr_city'])) ? $data['adr_city'] : null;
        $this->adr_state_id     = (isset($data['adr_state_id'])) ? $data['adr_state_id'] : null;
        $this->adr_zip     = (isset($data['adr_zip'])) ? $data['adr_zip'] : null;
        $this->adr_name     = (isset($data['adr_name'])) ? $data['adr_name'] : null;
        $this->adr_phone     = (isset($data['adr_phone'])) ? $data['adr_phone'] : null;
        $this->adr_phone_inner     = (isset($data['adr_phone_inner'])) ? $data['adr_phone_inner'] : null;
        $this->adr_other_phone     = (isset($data['adr_other_phone'])) ? $data['adr_other_phone'] : null;
        $this->adr_other_phone_inner     = (isset($data['adr_other_phone_inner'])) ? $data['adr_other_phone_inner'] : null;
        $this->adr_fax     = (isset($data['adr_fax'])) ? $data['adr_fax'] : null;
        $this->adr_email     = (isset($data['adr_email'])) ? $data['adr_email'] : null;

        $this->_state_name     = (isset($data['_state_name'])) ? $data['_state_name'] : null;
        $this->_state_code     = (isset($data['_state_code'])) ? $data['_state_code'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}