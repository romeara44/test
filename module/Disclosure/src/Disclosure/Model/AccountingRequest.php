<?php
namespace Disclosure\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AccountingRequest
{
    public $ar_id;
    public $ar_location;
    public $ar_requested_by;
    public $ar_date_requested;
    public $ar_disclosure_address;
    public $ar_patient_name;
    public $ar_medical_record_number;
    public $ar_date_of_birth;
    public $ar_patient_address;
    public $ar_date_requested_from;
    public $ar_date_requested_to;
    public $ar_fees_charge;
    public $ar_is_fees;
    public $ar_is_extensions;
    public $ar_extension_reason;
    public $ar_is_finalized;
    public $ar_date_sent;
    public $ar_date_patient_notified;
    public $ar_staff_member;
    public $ar_create_u_id;
    public $ar_active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->ar_id                   = (isset($data['ar_id']))                   ? $data['ar_id']                   : null;
        $this->ar_location                   = (isset($data['ar_location']))                   ? $data['ar_location']                   : null;
        $this->ar_requested_by     = (isset($data['ar_requested_by']))     ? $data['ar_requested_by']     : null;
        $this->ar_date_requested         = (isset($data['ar_date_requested']))         ? $data['ar_date_requested']         : null;
        $this->ar_disclosure_address       = (isset($data['ar_disclosure_address']))       ? $data['ar_disclosure_address']       : null;
        $this->ar_patient_name = (isset($data['ar_patient_name'])) ? $data['ar_patient_name'] : null;
        $this->ar_medical_record_number         = (isset($data['ar_medical_record_number']))         ? $data['ar_medical_record_number']         : null;
        $this->ar_date_of_birth   = (isset($data['ar_date_of_birth']))   ? $data['ar_date_of_birth']   : null;
        $this->ar_patient_address        = (isset($data['ar_patient_address']))        ? $data['ar_patient_address']        : null;
        $this->ar_date_requested_from         = (isset($data['ar_date_requested_from']))         ? $data['ar_date_requested_from']         : null;
        $this->ar_date_requested_to       = (isset($data['ar_date_requested_to']))       ? $data['ar_date_requested_to']       : null;
        $this->ar_fees_charge = (isset($data['ar_fees_charge'])) ? $data['ar_fees_charge'] : null;
        $this->ar_is_fees         = (isset($data['ar_is_fees']))         ? $data['ar_is_fees']         : null;
        $this->ar_is_extensions   = (isset($data['ar_is_extensions']))   ? $data['ar_is_extensions']   : null;
        $this->ar_extension_reason        = (isset($data['ar_extension_reason']))        ? $data['ar_extension_reason']        : null;
        $this->ar_is_finalized         = (isset($data['ar_is_finalized']))         ? $data['ar_is_finalized']         : null;
        $this->ar_date_sent       = (isset($data['ar_date_sent']))       ? $data['ar_date_sent']       : null;
        $this->ar_date_patient_notified = (isset($data['ar_date_patient_notified'])) ? $data['ar_date_patient_notified'] : null;
        $this->ar_staff_member         = (isset($data['ar_staff_member']))         ? $data['ar_staff_member']         : null;
        $this->ar_create_u_id          = (isset($data['ar_create_u_id']))          ? $data['ar_create_u_id']          : null;
        $this->ar_active               = (isset($data['ar_active']))               ? $data['ar_active']               : null;
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
                'name'     => 'ar_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ar_requested_by',
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