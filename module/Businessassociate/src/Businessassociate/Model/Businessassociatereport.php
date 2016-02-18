<?php
namespace Businessassociate\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Businessassociatereport
{
    public $bar_id;
    public $bar_ba_id;
    public $bar_f_id;
    public $bar_create_u_id;
    public $bar_create_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->bar_id     = (isset($data['bar_id'])) ? $data['bar_id'] : null;
        $this->bar_ba_id     = (isset($data['bar_ba_id'])) ? $data['bar_ba_id'] : null;
        $this->bar_f_id     = (isset($data['bar_f_id'])) ? $data['bar_f_id'] : null;
        $this->bar_create_u_id     = (isset($data['bar_create_u_id'])) ? $data['bar_create_u_id'] : null;
        $this->bar_create_date     = (isset($data['bar_create_date'])) ? $data['bar_create_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}