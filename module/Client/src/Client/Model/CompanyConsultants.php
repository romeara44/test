<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyConsultants
{
    public $cc_company_id;
    public $cc_consultant_id;
    public $_u_id;
    public $_u_firstname;
    public $_u_lastname;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->cc_company_id    = (isset($data['cc_company_id'])) ? $data['cc_company_id'] : null;
        $this->cc_consultant_id = (isset($data['cc_consultant_id'])) ? $data['cc_consultant_id'] : null;
        $this->_u_id            = (isset($data['_u_id'])) ? $data['_u_id'] : null;
        $this->_u_firstname     = (isset($data['_u_firstname'])) ? $data['_u_firstname'] : null;
        $this->_u_lastname      = (isset($data['_u_lastname'])) ? $data['_u_lastname'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}