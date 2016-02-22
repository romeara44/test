<?php
namespace Physicalsecuritychange\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Physicalsecuritychange
{
    public $psc_id;
    public $psc_c_id;
    public $psc_adr_id;
    public $psc_change_type;
    public $psc_create_u_id;
    public $psc_create_date;
    public $psc_active;

    public $_company_name;
    public $_location;
    public $_type;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->psc_id           = (isset($data['psc_id']))           ? $data['psc_id']           : null;
        $this->psc_c_id           = (isset($data['psc_c_id']))           ? $data['psc_c_id']           : null;
        $this->psc_adr_id               = (isset($data['psc_adr_id']))               ? $data['psc_adr_id']               : null;
        $this->psc_change_type            = (isset($data['psc_change_type']))            ? $data['psc_change_type']            : null;
        $this->psc_create_u_id           = (isset($data['psc_create_u_id']))           ? $data['psc_create_u_id']           : null;
        $this->psc_create_date      = (isset($data['psc_create_date']))      ? $data['psc_create_date']      : null;
        $this->psc_active      = (isset($data['psc_active']))      ? $data['psc_active']      : null;

        $this->_company_name      = (isset($data['_company_name']))      ? $data['_company_name']      : null;
        $this->_location      = (isset($data['_location']))      ? $data['_location']      : null;
        $this->_type      = (isset($data['_type']))      ? $data['_type']      : null;
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
                'name'     => 'tl_company_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'tl_id',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'tl_title',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'tl_tlt_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => '_tl_type_name',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}