<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyMasterRole
{
    public $company_master_role_id;
    public $role_name;
    public $active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->company_master_role_id   = (isset($data['company_master_role_id'])) ? $data['company_master_role_id'] : null;
        $this->role_name                = (isset($data['role_name'])) ? $data['role_name'] : null;
        $this->active                   = (isset($data['active'])) ? $data['active'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}