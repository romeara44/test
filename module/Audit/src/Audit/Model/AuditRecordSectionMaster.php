<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordSectionMaster
{
    
    public $audit_record_section_master_id;
    public $audit_record_type_id;
    public $audit_performance_criteria_id;
    public $active;
    public $create_u_id;
    public $modified_u_id;
    
    
    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_section_master_id   = (isset($data['audit_record_section_master_id'])) ? $data['audit_record_section_master_id'] : null;
        $this->audit_record_type_id             = (isset($data['audit_record_type_id'])) ? $data['audit_record_type_id'] : null;
        $this->audit_performance_criteria_id    = (isset($data['audit_performance_criteria_id'])) ? $data['audit_performance_criteria_id'] : null;
        $this->active                           = (isset($data['active'])) ? $data['active'] : null;
        $this->create_u_id                      = (isset($data['create_u_id'])) ? $data['create_u_id'] : null;
        $this->modified_u_id                    = (isset($data['modified_u_id'])) ? $data['modified_u_id'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}