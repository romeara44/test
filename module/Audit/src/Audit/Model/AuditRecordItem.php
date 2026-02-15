<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordItem
{
    
    public $audit_record_item_id;
    public $audit_inquiry_id;
    public $auditor_u_id;
    public $audited_date;
    public $item_status_id;
    public $audit_record_section_id;

    public $_auditor_name;
    public $_audit_performance_criteria_id;
    public $_audit_question_type_id;
    public $_key_activity;
    public $_performance_description;
    public $_policy_number;
    public $_inquiry_description;
    public $_check_mark_required;
    public $_notes;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_item_id             = (isset($data['audit_record_item_id'])) ? $data['audit_record_item_id'] : null;
        $this->audit_inquiry_id                 = (isset($data['audit_inquiry_id'])) ? $data['audit_inquiry_id'] : null;
        $this->auditor_u_id                     = (isset($data['auditor_u_id'])) ? $data['auditor_u_id'] : null;
        $this->audited_date                     = (isset($data['audited_date'])) ? $data['audited_date'] : null;
        $this->item_status_id                   = (isset($data['item_status_id'])) ? $data['item_status_id'] : null;
        $this->audit_record_section_id          = (isset($data['audit_record_section_id'])) ? $data['audit_record_section_id'] : null;

        $this->_auditor_name                    = (isset($data['_auditor_name'])) ? $data['_auditor_name'] : null;
        $this->_audit_performance_criteria_id   = (isset($data['_audit_performance_criteria_id'])) ? $data['_audit_performance_criteria_id'] : null;

        //audit_performance_criteria Table
        $this->_audit_question_type_id          = (isset($data['_audit_question_type_id'])) ? $data['_audit_question_type_id'] : null;
        $this->_key_activity                    = (isset($data['_key_activity'])) ? $data['_key_activity'] : null;
        $this->_performance_description         = (isset($data['_performance_description'])) ? $data['_performance_description'] : null;
         
        //audit_inquiry Table
        $this->_policy_number                   = (isset($data['_policy_number'])) ? $data['_policy_number'] : null;
        $this->_inquiry_description             = (isset($data['_inquiry_description'])) ? $data['_inquiry_description'] : null;
        $this->_check_mark_required             = (isset($data['_check_mark_required'])) ? $data['_check_mark_required'] : null;

        $this->_notes                           = (isset($data['_notes'])) ? $data['_notes'] : null;
        
    }

    public function exchangeObject($data)
    {
        $this->audit_record_item_id             = (isset($data->audit_record_item_id)) ? $data->audit_record_item_id : null;
        $this->audit_inquiry_id                 = (isset($data->audit_inquiry_id)) ? $data->audit_inquiry_id : null;
        $this->auditor_u_id                     = (isset($data->auditor_u_id)) ? $data->auditor_u_id : null;
        $this->audited_date                     = (isset($data->audited_date)) ? $data->audited_date : null;
        $this->item_status_id                   = (isset($data->item_status_id)) ? $data->item_status_id : null;
        $this->audit_record_section_id          = (isset($data->audit_record_section_id)) ? $data->audit_record_section_id : null;

        $this->_auditor_name                    = (isset($data->_auditor_name)) ? $data->_auditor_name : null;
        $this->_audit_performance_criteria_id   = (isset($data->_audit_performance_criteria_id)) ? $data->_audit_performance_criteria_id : null;

        //audit_performance_criteria Table
        $this->_audit_question_type_id          = (isset($data->_audit_question_type_id)) ? $data->_audit_question_type_id : null;
        $this->_key_activity                    = (isset($data->_key_activity)) ? $data->_key_activity : null;
        $this->_performance_description         = (isset($data->_performance_description)) ? $data->_performance_description : null;
         
        //audit_inquiry Table
        $this->_policy_number                   = (isset($data->_policy_number)) ? $data->_policy_number : null;
        $this->_inquiry_description             = (isset($data->_inquiry_description)) ? $data->_inquiry_description : null;
        $this->_check_mark_required             = (isset($data->_check_mark_required)) ? $data->_check_mark_required : null;

        $this->_notes                           = (isset($data->_notes)) ? $data->_notes : null;
        
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}