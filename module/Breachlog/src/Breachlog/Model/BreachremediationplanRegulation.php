<?php
namespace Breachlog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class BreachremediationplanRegulation
{

    public $brprg_id;
    public $brprg_brp_id;
    public $brprg_rg_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->brprg_id    = (isset($data['brprg_id'])) ? $data['brprg_id'] : null;
        $this->brprg_brp_id = (isset($data['brprg_brp_id'])) ? $data['brprg_brp_id'] : null;
        $this->brprg_rg_id = (isset($data['brprg_rg_id'])) ? $data['brprg_rg_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}