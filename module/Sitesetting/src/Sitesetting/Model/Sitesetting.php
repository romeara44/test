<?php
namespace Sitesetting\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Sitesetting
{

    const NUMBER_USERS_OF_COMPANY = 'number_users_of_company';
    
    public $ss_id;
    public $ss_name;
    public $ss_title;
    public $ss_value;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->ss_id    = (isset($data['ss_id']))    ? $data['ss_id']    : null;
        $this->ss_name  = (isset($data['ss_name']))  ? $data['ss_name']  : null;
        $this->ss_title = (isset($data['ss_title'])) ? $data['ss_title'] : null;
        $this->ss_value = (isset($data['ss_value'])) ? $data['ss_value'] : null;
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
                'name'     => 'ss_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ss_name',
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
        }
        
        return $this->inputFilter;
    }

}