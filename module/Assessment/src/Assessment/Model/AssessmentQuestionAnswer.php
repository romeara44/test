<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentQuestionAnswer
{
    public $aqa_id;
    public $aqa_a_id;
    public $aqa_adr_id;
    public $aqa_ar_id;
    public $aqa_aq_id;
    public $aqa_aqo_id;
    public $_score;
    public $aqa_active;
    public $aqa_create_u_id;
    public $aqa_update_u_id;
    public $aqa_create_date;
    public $aqa_update_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->aqa_id     = (isset($data['aqa_id'])) ? $data['aqa_id'] : null;
        $this->aqa_a_id     = (isset($data['aqa_a_id'])) ? $data['aqa_a_id'] : null;
        $this->aqa_adr_id     = (isset($data['aqa_adr_id'])) ? $data['aqa_adr_id'] : null;
        $this->aqa_ar_id     = (isset($data['aqa_ar_id'])) ? $data['aqa_ar_id'] : null;
        $this->aqa_aq_id     = (isset($data['aqa_aq_id'])) ? $data['aqa_aq_id'] : null;
        $this->aqa_aqo_id     = (isset($data['aqa_aqo_id'])) ? $data['aqa_aqo_id'] : null;
        $this->aqa_active     = (isset($data['aqa_active'])) ? $data['aqa_active'] : null;
        $this->_score     = (isset($data['_score'])) ? $data['_score'] : null;
        $this->aqa_create_u_id     = (isset($data['aqa_create_u_id'])) ? $data['aqa_create_u_id'] : null;
        $this->aqa_update_u_id     = (isset($data['aqa_update_u_id'])) ? $data['aqa_update_u_id'] : null;
        $this->aqa_create_date     = (isset($data['aqa_create_date'])) ? $data['aqa_create_date'] : null;
        $this->aqa_update_date     = (isset($data['aqa_update_date'])) ? $data['aqa_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}