<?php
namespace Client\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CompanyRoles
{
    public $cr_id;
    public $cr_ar_id;
    public $cr_c_id;
    public $cr_u_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->cr_id    = (isset($data['cr_id']))    ? $data['cr_id']    : null;
        $this->cr_ar_id = (isset($data['cr_ar_id'])) ? $data['cr_ar_id'] : null;
        $this->cr_c_id  = (isset($data['cr_c_id']))  ? $data['cr_c_id']  : null;
        $this->cr_u_id  = (isset($data['cr_u_id']))  ? $data['cr_u_id']  : null;
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
                'name'     => 'cr_id',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'cr_ar_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'cr_c_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'cr_u_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}