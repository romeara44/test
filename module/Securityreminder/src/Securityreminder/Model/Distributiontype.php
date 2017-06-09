<?php
namespace Securityreminder\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Distributiontype
{
    public $dt_id;
    public $dt_type;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->dt_id   = (isset($data['dt_id']))   ? $data['dt_id']   : null;
        $this->dt_type = (isset($data['dt_type'])) ? $data['dt_type'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl, $isEdit = false)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dt_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dt_type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}