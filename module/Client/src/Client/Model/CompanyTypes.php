<?php

namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CompanyTypes
{
    public $company_type_id;
    public $name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->company_type_id = (isset($data['company_type_id'])) ? $data['company_type_id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}
