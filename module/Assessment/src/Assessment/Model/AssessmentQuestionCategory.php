<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentQuestionCategory
{
    public $aqc_id;
    public $aqc_ar_id;
    public $aqc_parent_id;
    public $aqc_name;
    public $aqc_citation;
    public $aqc_policy;
    public $aqc_specification;
    public $aqc_action_plan;
    public $aqc_description;
    public $aqc_active;
    public $aqc_create_date;
    public $aqc_update_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->aqc_id     = (isset($data['aqc_id'])) ? $data['aqc_id'] : null;
        $this->aqc_parent_id     = (isset($data['aqc_parent_id'])) ? $data['aqc_parent_id'] : null;
        $this->aqc_ar_id     = (isset($data['aqc_ar_id'])) ? $data['aqc_ar_id'] : null;
        $this->aqc_name     = (isset($data['aqc_name'])) ? $data['aqc_name'] : null;
        $this->aqc_citation     = (isset($data['aqc_citation'])) ? $data['aqc_citation'] : null;
        $this->aqc_policy     = (isset($data['aqc_policy'])) ? $data['aqc_policy'] : null;
        $this->aqc_specification     = (isset($data['aqc_specification'])) ? $data['aqc_specification'] : null;
        $this->aqc_description     = (isset($data['aqc_description'])) ? $data['aqc_description'] : null;
        $this->aqc_action_plan     = (isset($data['aqc_action_plan'])) ? $data['aqc_action_plan'] : null;
        $this->aqc_active     = (isset($data['aqc_active'])) ? $data['aqc_active'] : null;
        $this->aqc_create_date     = (isset($data['aqc_create_date'])) ? $data['aqc_create_date'] : null;
        $this->aqc_update_date     = (isset($data['aqc_update_date'])) ? $data['aqc_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}