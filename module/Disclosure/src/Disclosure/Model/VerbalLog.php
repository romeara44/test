<?php
namespace Disclosure\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class VerbalLog
{
    public $vl_id;
    public $vl_date_of_request;
    public $vl_medical_record_number;
    public $vl_name;
    public $vl_date_of_birth;
    public $vl_address;
    public $vl_disclosure_address;
    public $vl_date_requested_from;
    public $vl_date_requested_to;
    public $vl_is_fees;
    public $vl_fees_charge;
    public $vl_date_request_received;
    public $vl_date_account_sent;
    public $vl_is_extensions;
    public $vl_extension_reason;
    public $vl_date_patient_notified;
    public $vl_staff_member;
    public $vl_create_u_id;
    public $vl_active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->vl_id                    = (isset($data['vl_id']))                    ? $data['vl_id']                    : null;
        $this->vl_date_of_request       = (isset($data['vl_date_of_request']))       ? $data['vl_date_of_request']       : null;
        $this->vl_medical_record_number = (isset($data['vl_medical_record_number'])) ? $data['vl_medical_record_number'] : null;
        $this->vl_name                  = (isset($data['vl_name']))                  ? $data['vl_name']                  : null;
        $this->vl_date_of_birth         = (isset($data['vl_date_of_birth']))         ? $data['vl_date_of_birth']         : null;
        $this->vl_address               = (isset($data['vl_address']))               ? $data['vl_address']               : null;
        $this->vl_disclosure_address    = (isset($data['vl_disclosure_address']))    ? $data['vl_disclosure_address']    : null;
        $this->vl_date_requested_from   = (isset($data['vl_date_requested_from']))   ? $data['vl_date_requested_from']   : null;
        $this->vl_date_requested_to     = (isset($data['vl_date_requested_to']))     ? $data['vl_date_requested_to']     : null;
        $this->vl_is_fees               = (isset($data['vl_is_fees']))               ? $data['vl_is_fees']               : null;
        $this->vl_fees_charge           = (isset($data['vl_fees_charge']))           ? $data['vl_fees_charge']           : null;
        $this->vl_date_request_received = (isset($data['vl_date_request_received'])) ? $data['vl_date_request_received'] : null;
        $this->vl_date_account_sent     = (isset($data['vl_date_account_sent']))     ? $data['vl_date_account_sent']     : null;
        $this->vl_is_extensions         = (isset($data['vl_is_extensions']))         ? $data['vl_is_extensions']         : null;
        $this->vl_extension_reason      = (isset($data['vl_extension_reason']))      ? $data['vl_extension_reason']      : null;
        $this->vl_date_patient_notified = (isset($data['vl_date_patient_notified'])) ? $data['vl_date_patient_notified'] : null;
        $this->vl_staff_member          = (isset($data['vl_staff_member']))          ? $data['vl_staff_member']          : null;
        $this->vl_create_u_id           = (isset($data['vl_create_u_id']))           ? $data['vl_create_u_id']           : null;
        $this->vl_active                = (isset($data['vl_active']))                ? $data['vl_active']                : null;
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
                'name'     => 'vl_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_active',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_is_fees',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_is_extensions',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_medical_record_number',
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
                'name'     => 'vl_name',
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
                'name'     => 'vl_date_of_birth',
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
                'name'     => 'vl_date_requested_from',
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
                'name'     => 'vl_date_requested_to',
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
                'name'     => 'vl_date_request_received',
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
                'name'     => 'vl_date_account_sent',
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
                'name'     => 'vl_date_patient_notified',
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
                'name'     => 'vl_address',
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
                'name'     => 'vl_disclosure_address',
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
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_extension_reason',
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
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'vl_staff_member',
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
                'name'     => 'vl_fees_charge',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Float',
                        'options' => array(
                            'min' => 0,
                            'locale' => 'en_US',
                            'messages' => array(
                                 \Zend\I18n\Validator\Float::NOT_FLOAT => "The input value must be a correct numeric"
                            )
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}