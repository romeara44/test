<?php
namespace Securityreminder\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Securityreminder
{
    public $sr_id;
    public $sr_dt_id;
    public $sr_create_u_id;
    public $sr_launched_date;
    public $sr_developed_by;
    public $sr_regulation;
    public $sr_active;

    public $_dt_type;
    public $_sr_comment;
    public $_sr_attachment;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->sr_id            = (isset($data['sr_id']))             ? $data['sr_id']             : null;
        $this->sr_dt_id         = (isset($data['sr_dt_id']))          ? $data['sr_dt_id']          : null;
        $this->sr_create_u_id   = (isset($data['sr_create_u_id']))    ? $data['sr_create_u_id']    : null;
        $this->sr_launched_date = (isset($data['sr_launched_date']))  ? $data['sr_launched_date']  : null;
        $this->sr_developed_by  = (isset($data['sr_developed_by']))   ? $data['sr_developed_by']   : null;
        $this->sr_regulation    = (isset($data['sr_regulation']))     ? $data['sr_regulation']     : null;
        $this->sr_active        = (isset($data['sr_active']))         ? $data['sr_active']         : null;
        $this->_dt_type         = (isset($data['_dt_type']))          ? $data['_dt_type']          : null;
        $this->_sr_comment      = (isset($data['_sr_comment']))       ? $data['_sr_comment']       : null;
        $this->_sr_attachment   = (isset($data['_sr_attachment']))    ? $data['_sr_attachment']    : null;
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
                'name'     => 'sr_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'sr_dt_id',
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