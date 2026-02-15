<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyModuleRole
{
    public $company_module_role_id;
    public $company_id;
    public $hipaa_suite_module_role_id;
    public $user_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->company_module_role_id       = (isset($data['company_module_role_id'])) ? $data['company_module_role_id'] : null;
        $this->company_id                   = (isset($data['company_id'])) ? $data['company_id'] : null;
        $this->hipaa_suite_module_role_id   = (isset($data['hipaa_suite_module_role_id'])) ? $data['hipaa_suite_module_role_id'] : null;
        $this->user_id                      = (isset($data['user_id'])) ? $data['user_id'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}