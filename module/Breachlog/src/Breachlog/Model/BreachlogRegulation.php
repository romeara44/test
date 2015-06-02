<?php
namespace Breachlog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class BreachlogRegulation
{

    public $blrg_id;
    public $blrg_bl_id;
    public $blrg_rg_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->blrg_id    = (isset($data['blrg_id'])) ? $data['blrg_id'] : null;
        $this->blrg_bl_id = (isset($data['blrg_bl_id'])) ? $data['blrg_bl_id'] : null;
        $this->blrg_rg_id = (isset($data['blrg_rg_id'])) ? $data['blrg_rg_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}