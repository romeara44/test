<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditPerformanceCriteria
{
    
    public $audit_performance_criteria_id;
    public $question_type_id;
    public $policy_number;
    public $key_activity;
    public $description;
    public $active;
    public $display_order;
    public $creation_date;
    public $modified_date;

    public $_audit_items;

    protected $inputFilter;

    public function exchangeArray($data)
        {
            $this->audit_performance_criteria_id    = (isset($data['audit_performance_criteria_id'])) ? $data['audit_performance_criteria_id'] : null;
            $this->audit_question_type_id           = (isset($data['audit_question_type_id'])) ? $data['audit_question_type_id'] : null;
            $this->policy_number                    = (isset($data['policy_number'])) ? $data['policy_number'] : null;
            $this->key_activity                     = (isset($data['key_activity'])) ? $data['key_activity'] : null;
            $this->description                      = (isset($data['description'])) ? $data['description'] : null;
            $this->active                           = (isset($data['active'])) ? $data['active'] : null;
            $this->display_order                    = (isset($data['display_order'])) ? $data['display_order'] : null;
            $this->creation_date                    = (isset($data['creation_date'])) ? $data['creation_date'] : null;
            $this->modified_date                    = (isset($data['modified_date'])) ? $data['modified_date'] : null;

            $this->_audit_items                     = (isset($data['_audit_items'])) ? $data['_audit_items'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}