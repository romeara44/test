<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyModuleRoleAlias
{
    public $audit_role_location_contact_id;
    public $audit_record_id;
    public $address_id;
    public $hipaa_suite_module_role_id;
    public $user_id;
    public $active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_role_location_contact_id   = (isset($data['audit_role_location_contact_id'])) ? $data['audit_role_location_contact_id'] : null;
        $this->audit_record_id                  = (isset($data['audit_record_id'])) ? $data['audit_record_id'] : null;
        $this->address_id                       = (isset($data['address_id'])) ? $data['address_id'] : null;
        $this->hipaa_suite_module_role_id       = (isset($data['hipaa_suite_module_role_id'])) ? $data['hipaa_suite_module_role_id'] : null;
        $this->user_id                          = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->actived                          = (isset($data['active'])) ? $data['active'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}