<?php
namespace Disclosure\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DisclosureTrackingLog
{
    public $dtl_id;
    public $dtl_reference_number;
    public $dtl_patient_name;
    public $dtl_medical_record_number;
    public $dtl_date_received;
    public $dtl_name_of_requestor;
    public $dtl_address;
    public $dtl_auth_type;
    public $dtl_purpose_of_disclosure;
    public $dtl_phi_information_disclosed;
    public $dtl_date_disclosed;
    public $dtl_disclosed_by;
    public $dtl_extension_notification;
    public $dtl_copy_of_request;
    public $dtl_create_u_id;
    public $dtl_active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->dtl_id                        = (isset($data['dtl_id']))                        ? $data['dtl_id']                        : null;
        $this->dtl_reference_number          = (isset($data['dtl_reference_number']))          ? $data['dtl_reference_number']          : null;
        $this->dtl_patient_name              = (isset($data['dtl_patient_name']))              ? $data['dtl_patient_name']              : null;
        $this->dtl_medical_record_number     = (isset($data['dtl_medical_record_number']))     ? $data['dtl_medical_record_number']     : null;
        $this->dtl_date_received             = (isset($data['dtl_date_received']))             ? $data['dtl_date_received']             : null;
        $this->dtl_name_of_requestor         = (isset($data['dtl_name_of_requestor']))         ? $data['dtl_name_of_requestor']         : null;
        $this->dtl_address                   = (isset($data['dtl_address']))                   ? $data['dtl_address']                   : null;
        $this->dtl_auth_type                 = (isset($data['dtl_auth_type']))                 ? $data['dtl_auth_type']                 : null;
        $this->dtl_purpose_of_disclosure     = (isset($data['dtl_purpose_of_disclosure']))     ? $data['dtl_purpose_of_disclosure']     : null;
        $this->dtl_phi_information_disclosed = (isset($data['dtl_phi_information_disclosed'])) ? $data['dtl_phi_information_disclosed'] : null;
        $this->dtl_date_disclosed            = (isset($data['dtl_date_disclosed']))            ? $data['dtl_date_disclosed']            : null;
        $this->dtl_disclosed_by              = (isset($data['dtl_disclosed_by']))              ? $data['dtl_disclosed_by']              : null;
        $this->dtl_extension_notification    = (isset($data['dtl_extension_notification']))    ? $data['dtl_extension_notification']    : null;
        $this->dtl_copy_of_request           = (isset($data['dtl_copy_of_request']))           ? $data['dtl_copy_of_request']           : null;
        $this->dtl_create_u_id               = (isset($data['dtl_create_u_id']))               ? $data['dtl_create_u_id']               : null;
        $this->dtl_active                    = (isset($data['dtl_active']))                    ? $data['dtl_active']                    : null;
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
                'name'     => 'dtl_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dtl_active',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'dtl_reference_number',
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
                'name'     => 'dtl_disclosed_by',
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