<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Trainer
{

    public $tr_id;
    public $tr_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tr_id   = (isset($data['tr_id']))   ? $data['tr_id']   : null;
        $this->tr_name = (isset($data['tr_name'])) ? $data['tr_name'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}