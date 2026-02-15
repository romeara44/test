<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditInquiry
{
    
    public $audit_inquiry_id;
    public $audit_performance_criteria_id;
    public $policy_number;
    public $description;
    public $check_mark_required;
    public $active;
    public $display_order;
    public $creation_date;
    public $modified_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_inquiry_id                  = (isset($data['audit_inquiry_id'])) ? $data['audit_inquiry_id'] : null;
        $this->audit_performance_criteria_id     = (isset($data['audit_performance_criteria_id'])) ? $data['audit_performance_criteria_id'] : null;
        $this->policy_number                     = (isset($data['policy_number'])) ? $data['policy_number'] : null;
        $this->description                       = (isset($data['description'])) ? $data['description'] : null;
        $this->check_mark_required               = (isset($data['check_mark_required'])) ? $data['check_mark_required'] : null;
        $this->active                            = (isset($data['active'])) ? $data['active'] : null;
        $this->display_order                     = (isset($data['display_order'])) ? $data['display_order'] : null;
        $this->creation_date                     = (isset($data['creation_date'])) ? $data['creation_date'] : null;
        $this->audit_status                      = (isset($data['audit_status'])) ? $data['audit_status'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}