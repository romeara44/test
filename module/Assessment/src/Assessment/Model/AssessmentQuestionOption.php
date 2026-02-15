<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentQuestionOption
{
    public $aqo_id;
    public $aqo_aq_id;
    public $aqo_title;
    public $aqo_order;
    public $aqo_risk_score;
    public $aqo_active;
    public $calculate_risk_score;
    public $impact_value;
    public $likelihood_value;
    public $aqo_create_date;
    public $aqo_update_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->aqo_id     = (isset($data['aqo_id'])) ? $data['aqo_id'] : null;
        $this->aqo_aq_id     = (isset($data['aqo_aq_id'])) ? $data['aqo_aq_id'] : null;
        $this->aqo_title     = (isset($data['aqo_title'])) ? $data['aqo_title'] : null;
        $this->aqo_order     = (isset($data['aqo_order'])) ? $data['aqo_order'] : null;
        $this->aqo_risk_score     = (isset($data['aqo_risk_score'])) ? $data['aqo_risk_score'] : null;
        $this->aqo_active     = (isset($data['aqo_active'])) ? $data['aqo_active'] : null;
        $this->calculate_risk_score = (isset($data['calculate_risk_score'])) ? $data['calculate_risk_score'] : null;
        $this->impact_value   = (isset($data['impact_value'])) ? $data['impact_value'] : null;
        $this->likelihood_value = (isset($data['likelihood_value'])) ? $data['likelihood_value'] : null;
        $this->aqo_create_date     = (isset($data['aqo_create_date'])) ? $data['aqo_create_date'] : null;
        $this->aqo_update_date     = (isset($data['aqo_update_date'])) ? $data['aqo_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}