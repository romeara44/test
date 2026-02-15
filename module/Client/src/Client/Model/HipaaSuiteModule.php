<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class HipaaSuiteModule
{
    public $hipaa_suite_module_id;
    public $module_name;
    public $module_name_display;
    public $active;
    public $display_order;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->hipaa_suite_module_id    = (isset($data['hipaa_suite_module_id'])) ? $data['hipaa_suite_module_id'] : null;
        $this->module_name              = (isset($data['module_name'])) ? $data['module_name'] : null;
        $this->module_name_display      = (isset($data['module_name_display'])) ? $data['module_name_display'] : null;
        $this->active                   = (isset($data['active'])) ? $data['active'] : null;
        $this->display_order            = (isset($data['display_order'])) ? $data['display_order'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}