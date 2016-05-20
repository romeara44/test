<?php
namespace Disclosure\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DisclosureRecord
{
    public $dr_id;
    public $dr_location;
    public $dr_date_received;
    public $dr_patient_name;
    public $dr_date_disclosure;
    public $dr_medical_record_number;
    public $dr_date_of_birth;
    public $dr_phi_information_disclosed;
    public $dr_purpose_of_disclosure;
    public $dr_name_of_requestor;
    public $dr_address;
    public $dr_is_verbal;
    public $dr_is_authorized;
    public $dr_entered_by;
    public $dr_date_entered;
    public $dr_reviewed_by;
    public $dr_date_reviewed;
    public $dr_disclosed_by;
    public $dr_date_disclosed;
    public $dr_create_u_id;
    public $dr_active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->dr_id                   = (isset($data['dr_id']))                   ? $data['dr_id']                   : null;
        $this->dr_location                   = (isset($data['dr_location']))                   ? $data['dr_location']                   : null;
        $this->dr_date_received     = (isset($data['dr_date_received']))     ? $data['dr_date_received']     : null;
        $this->dr_patient_name         = (isset($data['dr_patient_name']))         ? $data['dr_patient_name']         : null;
        $this->dr_date_disclosure       = (isset($data['dr_date_disclosure']))       ? $data['dr_date_disclosure']       : null;
        $this->dr_medical_record_number = (isset($data['dr_medical_record_number'])) ? $data['dr_medical_record_number'] : null;
        $this->dr_date_of_birth         = (isset($data['dr_date_of_birth']))         ? $data['dr_date_of_birth']         : null;
        $this->dr_phi_information_disclosed   = (isset($data['dr_phi_information_disclosed']))   ? $data['dr_phi_information_disclosed']   : null;
        $this->dr_purpose_of_disclosure        = (isset($data['dr_purpose_of_disclosure']))        ? $data['dr_purpose_of_disclosure']        : null;
        $this->dr_name_of_requestor         = (isset($data['dr_name_of_requestor']))         ? $data['dr_name_of_requestor']         : null;
        $this->dr_address       = (isset($data['dr_address']))       ? $data['dr_address']       : null;
        $this->dr_is_verbal = (isset($data['dr_is_verbal'])) ? $data['dr_is_verbal'] : null;
        $this->dr_is_authorized         = (isset($data['dr_is_authorized']))         ? $data['dr_is_authorized']         : null;
        $this->dr_entered_by   = (isset($data['dr_entered_by']))   ? $data['dr_entered_by']   : null;
        $this->dr_date_entered        = (isset($data['dr_date_entered']))        ? $data['dr_date_entered']        : null;
        $this->dr_reviewed_by         = (isset($data['dr_reviewed_by']))         ? $data['dr_reviewed_by']         : null;
        $this->dr_date_reviewed       = (isset($data['dr_date_reviewed']))       ? $data['dr_date_reviewed']       : null;
        $this->dr_disclosed_by = (isset($data['dr_disclosed_by'])) ? $data['dr_disclosed_by'] : null;
        $this->dr_date_disclosed         = (isset($data['dr_date_disclosed']))         ? $data['dr_date_disclosed']         : null;
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
                'name'     => 'dr_entered_by',
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