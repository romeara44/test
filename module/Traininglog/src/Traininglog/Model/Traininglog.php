<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Traininglog
{
    public $tl_id;
    public $tl_tlt_id;
    public $tl_create_u_id;
    public $tl_conducted_date;
    public $tl_hire_date;
    public $tl_trainer_id;
    public $tl_trainer_type;
    public $tl_attendees;
    public $tl_active;

    public $_tlt_name;
    public $_tl_comment;
    public $_tl_attachment;
    public $_tl_tlrg_id;
    public $_tl_tlrg_rg_id;
    public $_tl_regulation;
    public $_tl_cur_regulations;
    public $_regulation;
    public $_tl_trainer;
    public $_tl_trainer_name;
    public $_tl_type_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tl_id               = (isset($data['tl_id']))               ? $data['tl_id']               : null;
        $this->tl_tlt_id           = (isset($data['tl_tlt_id']))           ? $data['tl_tlt_id']           : null;
        $this->tl_create_u_id      = (isset($data['tl_create_u_id']))      ? $data['tl_create_u_id']      : null;
        $this->tl_conducted_date   = (isset($data['tl_conducted_date']))   ? $data['tl_conducted_date']   : null;
        $this->tl_hire_date        = (isset($data['tl_hire_date']))        ? $data['tl_hire_date']        : null;
        $this->tl_trainer_id       = (isset($data['tl_trainer_id']))       ? $data['tl_trainer_id']       : null;
        $this->tl_trainer_type     = (isset($data['tl_trainer_type']))     ? $data['tl_trainer_type']     : null;
        $this->tl_attendees        = (isset($data['tl_attendees']))        ? $data['tl_attendees']        : null;
        $this->tl_active           = (isset($data['tl_active']))           ? $data['tl_active']           : null;
        $this->_tlt_name           = (isset($data['_tlt_name']))           ? $data['_tlt_name']           : null;
        $this->_tl_comment         = (isset($data['_tl_comment']))         ? $data['_tl_comment']         : null;
        $this->_tl_attachment      = (isset($data['_tl_attachment']))      ? $data['_tl_attachment']      : null;
        $this->_tl_tlrg_id         = (isset($data['_tl_tlrg_id']))         ? $data['_tl_tlrg_id']         : null;
        $this->_tl_tlrg_rg_id      = (isset($data['_tl_tlrg_rg_id']))      ? $data['_tl_tlrg_rg_id']      : null;
        $this->_tl_regulation      = (isset($data['_tl_regulation']))      ? $data['_tl_regulation']      : null;
        $this->_tl_cur_regulations = (isset($data['_tl_cur_regulations'])) ? $data['_tl_cur_regulations'] : null;
        $this->_regulation         = (isset($data['_regulation']))         ? $data['_regulation']         : null;
        $this->_tl_trainer         = (isset($data['_tl_trainer']))         ? $data['_tl_trainer']         : null;
        $this->_tl_trainer_name    = (isset($data['_tl_trainer_name']))    ? $data['_tl_trainer_name']    : null;
        $this->_tl_type_name       = (isset($data['_tl_type_name']))       ? $data['_tl_type_name']       : null;
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
                'name'     => 'tl_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
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