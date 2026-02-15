<?php

namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class HipaaSuiteTypes
{
    public $hipaa_suite_type_id;
    public $hipaa_suite_description;
    public $display_description;
    public $column_order;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->hipaa_suite_type_id =        (isset($data['hipaa_suite_type_id'])) ? $data['hipaa_suite_type_id'] : null;
        $this->hipaa_suite_description =    (isset($data['hipaa_suite_description'])) ? $data['hipaa_suite_description'] : null;
        $this->display_description =        (isset($data['display_description'])) ? $data['display_description'] : null;
        $this->column_order =               (isset($data['column_order'])) ? $data['column_order'] : null;
        
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}