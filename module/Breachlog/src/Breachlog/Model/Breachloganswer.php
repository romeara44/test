<?php
namespace Breachlog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Breachloganswer
{
    public $bla_id;
    public $bla_blq_id;
    public $bla_bl_id;
    public $bla_u_id;
    public $bla_value;
    public $bla_active;
    public $bla_create_date;

    public $_blq_title;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->bla_id     = (isset($data['bla_id'])) ? $data['bla_id'] : null;
        $this->bla_blq_id     = (isset($data['bla_blq_id'])) ? $data['bla_blq_id'] : null;
        $this->bla_bl_id     = (isset($data['bla_bl_id'])) ? $data['bla_bl_id'] : null;
        $this->bla_u_id     = (isset($data['bla_u_id'])) ? $data['bla_u_id'] : null;
        $this->bla_value     = (isset($data['bla_value'])) ? $data['bla_value'] : null;
        $this->bla_active     = (isset($data['bla_active'])) ? $data['bla_active'] : null;
        $this->bla_create_date     = (isset($data['bla_create_date'])) ? $data['bla_create_date'] : null;

        $this->_blq_title     = (isset($data['_blq_title'])) ? $data['_blq_title'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}