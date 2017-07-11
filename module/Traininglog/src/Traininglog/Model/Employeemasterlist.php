<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Employeemasterlist
{
    public $eml_company_id;
    public $eml_list;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->eml_company_id           = (isset($data['eml_company_id']))           ? $data['eml_company_id']           : null;
        $this->eml_list            = (isset($data['eml_list']))            ? $data['eml_list']            : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}