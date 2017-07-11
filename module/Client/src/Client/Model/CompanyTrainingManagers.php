<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyTrainingManagers
{
    public $ctm_company_id;
    public $ctm_u_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->ctm_company_id    = (isset($data['ctm_company_id'])) ? $data['ctm_company_id'] : null;
        $this->ctm_u_id = (isset($data['ctm_u_id'])) ? $data['ctm_u_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}