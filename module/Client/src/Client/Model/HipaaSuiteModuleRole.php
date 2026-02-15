<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class HipaaSuiteModuleRole
{
    public $hipaa_suite_module_role_id;
    public $hipaa_suite_module_id;
    public $company_master_role_id;
    public $active;
    public $display_order;
    
    public $_role_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->hipaa_suite_module_role_id   = (isset($data['hipaa_suite_module_role_id'])) ? $data['hipaa_suite_module_role_id'] : null;
        $this->hipaa_suite_module_id        = (isset($data['hipaa_suite_module_id'])) ? $data['hipaa_suite_module_id'] : null;
        $this->company_master_role_id       = (isset($data['company_master_role_id'])) ? $data['company_master_role_id'] : null;
        $this->active                       = (isset($data['active'])) ? $data['active'] : null;
        $this->display_order                = (isset($data['display_order'])) ? $data['display_order'] : null;
        $this->_role_name                   = (isset($data['_role_name'])) ? $data['_role_name'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}