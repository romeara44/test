<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Regulation
{

    public $rg_id;
    public $rg_pp_name;
    public $rg_pp_number;
    public $rg_number;
    public $rg_description;
    public $rg_u_owner_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->rg_id          = (isset($data['rg_id'])) ? $data['rg_id'] : null;
        $this->rg_pp_name     = (isset($data['rg_pp_name'])) ? $data['rg_pp_name'] : null;
        $this->rg_pp_number   = (isset($data['rg_pp_number'])) ? $data['rg_pp_number'] : null;
        $this->rg_number      = (isset($data['rg_number'])) ? $data['rg_number'] : null;
        $this->rg_description = (isset($data['rg_description'])) ? $data['rg_description'] : null;
        $this->rg_u_owner_id  = (isset($data['rg_u_owner_id'])) ? $data['rg_u_owner_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}