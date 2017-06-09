<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentQuestion
{
    public $aq_id;
    public $aq_parent_aq_id;
    public $aq_aqc_id;
    public $aq_type;
    public $aq_title;
    public $aq_text;
    public $aq_order;
    public $aq_active;
    public $aq_create_date;
    public $aq_update_date;

    public $aqc_parent_id;
    public $aqc_name;
    public $aqc_citation;
    public $aqc_specification;
    public $aqc_description;
    public $aqc_policy;

    public $_options;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->aq_id     = (isset($data['aq_id'])) ? $data['aq_id'] : null;
        $this->aq_parent_aq_id     = (isset($data['aq_parent_aq_id'])) ? $data['aq_parent_aq_id'] : null;
        $this->aq_aqc_id     = (isset($data['aq_aqc_id'])) ? $data['aq_aqc_id'] : null;
        $this->aq_type     = (isset($data['aq_type'])) ? $data['aq_type'] : null;
        $this->aq_title     = (isset($data['aq_title'])) ? $data['aq_title'] : null;
        $this->aq_text     = (isset($data['aq_text'])) ? $data['aq_text'] : null;
        $this->aq_order     = (isset($data['aq_order'])) ? $data['aq_order'] : null;
        $this->aq_active     = (isset($data['aq_active'])) ? $data['aq_active'] : null;
        $this->aq_create_date     = (isset($data['aq_create_date'])) ? $data['aq_create_date'] : null;
        $this->aq_update_date     = (isset($data['aq_update_date'])) ? $data['aq_update_date'] : null;

        $this->aqc_parent_id     = (isset($data['aqc_parent_id'])) ? $data['aqc_parent_id'] : null;
        $this->aqc_name     = (isset($data['aqc_name'])) ? $data['aqc_name'] : null;
        $this->aqc_citation     = (isset($data['aqc_citation'])) ? $data['aqc_citation'] : null;
        $this->aqc_specification     = (isset($data['aqc_specification'])) ? $data['aqc_specification'] : null;
        $this->aqc_description     = (isset($data['aqc_description'])) ? $data['aqc_description'] : null;
        $this->aqc_policy     = (isset($data['aqc_policy'])) ? $data['aqc_policy'] : null;

        $this->_options     = (isset($data['_options'])) ? $data['_options'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl, $isEdit = false, $uId = 0)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'aq_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));
       }

        return $this->inputFilter;
    }

}