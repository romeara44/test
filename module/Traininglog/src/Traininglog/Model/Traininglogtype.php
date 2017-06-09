<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Traininglogtype
{
    public $tlt_id;
    public $tlt_name;
    public $tlt_company_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tlt_id         = (isset($data['tlt_id']))   ? $data['tlt_id']   : null;
        $this->tlt_name       = (isset($data['tlt_name'])) ? $data['tlt_name'] : null;
        $this->tlt_company_id = (isset($data['tlt_company_id'])) ? $data['tlt_company_id'] : null;
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
                'name'     => 'tlt_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'tlt_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'tlt_company_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}