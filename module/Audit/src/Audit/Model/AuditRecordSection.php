<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordSection
{
    
    public $audit_record_section_id;
    public $audit_record_type_id;
    public $audit_performance_criteria_id;
    public $create_u_id;
    public $modified_u_id;

    public $_create_name;
    public $_modified_name;

    public $_audit_question_type_id;
    public $_key_activity;
    public $_performance_description;
    public $_performance_policy_numbers;

    public $_audit_items;

    
    
    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_section_id          = (isset($data['audit_record_section_id'])) ? $data['audit_record_section_id'] : null;
        $this->audit_record_type_id             = (isset($data['audit_record_type_id'])) ? $data['audit_record_type_id'] : null;
        $this->audit_performance_criteria_id    = (isset($data['audit_performance_criteria_id'])) ? $data['audit_performance_criteria_id'] : null;
        $this->create_u_id                      = (isset($data['create_u_id'])) ? $data['create_u_id'] : null;
        $this->modified_u_id                    = (isset($data['modified_u_id'])) ? $data['modified_u_id'] : null;
        
        $this->_create_name                     = (isset($data['_create_name'])) ? $data['_create_name'] : null;
        $this->_modified_name                   = (isset($data['_modified_name'])) ? $data['_approve_modified_named_name'] : null;

        //audit_performance_criteria Table
        $this->_audit_question_type_id          = (isset($data['_audit_question_type_id'])) ? $data['_audit_question_type_id'] : null;
        $this->_key_activity                    = (isset($data['_key_activity'])) ? $data['_key_activity'] : null;
        $this->_performance_description         = (isset($data['_performance_description'])) ? $data['_performance_description'] : null;
        $this->_performance_policy_numbers      = (isset($data['_performance_policy_numbers'])) ? $data['_performance_policy_numbers'] : null;
        $this->_policy_number_notes             = (isset($data['_policy_number_notes'])) ? $data['_policy_number_notes'] : null;
        $this->_policy_number_files             = (isset($data['_policy_number_files'])) ? $data['_policy_number_files'] : null;
        //audit_inquiry Table
        $this->_audit_performance_criteria_object = (isset($data['_audit_performance_criteria'])) ? $data['_audit_performance_criteria'] : null;
        
        //audit_inquiry Table
        $this->_audit_items                     = (isset($data['_audit_items'])) ? $data['_audit_items'] : null;

    }

    public function exchangeObjectToArray($data)
    {
        $this->audit_record_section_id          = (isset($data['audit_record_section_id'])) ? $data['audit_record_section_id'] : null;
        $this->audit_performance_criteria_id    = (isset($data['audit_performance_criteria_id'])) ? $data['audit_performance_criteria_id'] : null;
        $this->create_u_id                      = (isset($data['create_u_id'])) ? $data['create_u_id'] : null;
        $this->modified_u_id                    = (isset($data['modified_u_id'])) ? $data['modified_u_id'] : null;
        
        $this->_auditor_name                    = (isset($data['_auditor_name'])) ? $data['_auditor_name'] : null;
        $this->_acceptor_name                   = (isset($data['_acceptor_name'])) ? $data['_acceptor_name'] : null;
        $this->_approved_name                   = (isset($data['_approved_name'])) ? $data['_approved_name'] : null;
        $this->_create_name                     = (isset($data['_create_name'])) ? $data['_create_name'] : null;
        $this->_modified_name                   = (isset($data['_modified_name'])) ? $data['_approve_modified_named_name'] : null;


        //audit_performance_criteria Table
        $this->_audit_question_type_id          = (isset($data['_audit_question_type_id'])) ? $data['_audit_question_type_id'] : null;
        $this->_key_activity                    = (isset($data['_key_activity'])) ? $data['_key_activity'] : null;
        $this->_performance_description         = (isset($data['_performance_description'])) ? $data['_performance_description'] : null;
        
        //audit_inquiry Table
        $this->_audit_performance_criteria_object = (isset($data['_audit_performance_criteria'])) ? $data['_audit_performance_criteria'] : null;
        $this->$_performance_policy_numbers     = (isset($data['_performance_policy_numbers'])) ? $data['_performance_policy_numbers'] : null;
        
        //audit_inquiry Table
        $this->_audit_items                     = (isset($data['_audit_items'])) ? $data['_audit_items'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}