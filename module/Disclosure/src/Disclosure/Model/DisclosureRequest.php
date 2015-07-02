<?php
namespace Disclosure\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DisclosureRequest
{
    public $dr_id;
    public $dr_reference_number;
    public $dr_requested_by;
    public $dr_date_requested;
    public $dr_date_range_requested;
    public $dr_staff_member;
    public $dr_completing_request;
    public $dr_date_provided;
    public $dr_create_u_id;
    public $dr_active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->dr_id                   = (isset($data['dr_id']))                   ? $data['dr_id']                   : null;
        $this->dr_reference_number     = (isset($data['dr_reference_number']))     ? $data['dr_reference_number']     : null;
        $this->dr_requested_by         = (isset($data['dr_requested_by']))         ? $data['dr_requested_by']         : null;
        $this->dr_date_requested       = (isset($data['dr_date_requested']))       ? $data['dr_date_requested']       : null;
        $this->dr_date_range_requested = (isset($data['dr_date_range_requested'])) ? $data['dr_date_range_requested'] : null;
        $this->dr_staff_member         = (isset($data['dr_staff_member']))         ? $data['dr_staff_member']         : null;
        $this->dr_completing_request   = (isset($data['dr_completing_request']))   ? $data['dr_completing_request']   : null;
        $this->dr_date_provided        = (isset($data['dr_date_provided']))        ? $data['dr_date_provided']        : null;
        $this->dr_create_u_id          = (isset($data['dr_create_u_id']))          ? $data['dr_create_u_id']          : null;
        $this->dr_active               = (isset($data['dr_active']))               ? $data['dr_active']               : null;
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
                'name'     => 'dr_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dr_reference_number',
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
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dr_requested_by',
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
                            'max'      => 255,
                        ),
                    ),
                ),
            )));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}