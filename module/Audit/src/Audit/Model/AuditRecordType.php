<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordType
{
    
    public $audit_record_type_id;
    public $audit_question_type_id;
    public $audit_record_id;
    public $auditor_u_id;
    public $consultant_u_id;
    public $performed_u_id;
    public $audit_start_date;
    public $audit_complete_date;
    public $acceptor_initials;
    public $acceptor_u_id;
    public $accepted_date;
    public $approved_initials;
    public $approved_u_id;
    public $approved_date;
    public $is_locked;
    public $create_u_id;
    public $modified_u_id;

    public $_auditor_name;
    public $_consultant_name;
    public $_performed_name;
    public $_acceptor_name;
    public $_approved_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_type_id     = (isset($data['audit_record_type_id'])) ? $data['audit_record_type_id'] : null;
        $this->audit_question_type_id   = (isset($data['audit_question_type_id'])) ? $data['audit_question_type_id'] : null;
        $this->audit_record_id          = (isset($data['audit_record_id'])) ? $data['audit_record_id'] : null;
        $this->auditor_u_id             = (isset($data['auditor_u_id'])) ? $data['auditor_u_id'] : null;
        $this->consultant_u_id          = (isset($data['consultant_u_id'])) ? $data['consultant_u_id'] : null;
        $this->performed_u_id           = (isset($data['performed_u_id'])) ? $data['performed_u_id'] : null;
        $this->audit_start_date         = (isset($data['audit_start_date'])) ? $data['audit_start_date'] : null;
        $this->audit_complete_date      = (isset($data['audit_complete_date'])) ? $data['audit_complete_date'] : null;
        $this->acceptor_initials        = (isset($data['acceptor_initials'])) ? $data['acceptor_initials'] : null;
        $this->acceptor_u_id            = (isset($data['acceptor_u_id'])) ? $data['acceptor_u_id'] : null;
        $this->accepted_date            = (isset($data['accepted_date'])) ? $data['accepted_date'] : null;
        $this->approved_initials        = (isset($data['approved_initials'])) ? $data['approved_initials'] : null;
        $this->approved_u_id            = (isset($data['approved_u_id'])) ? $data['approved_u_id'] : null;
        $this->approved_date            = (isset($data['approved_date'])) ? $data['approved_date'] : null;
        $this->is_locked                = (isset($data['is_locked'])) ? $data['is_locked'] : null;
        $this->create_u_id              = (isset($data['create_u_id'])) ? $data['create_u_id'] : null;
        $this->modified_u_id            = (isset($data['modified_u_id'])) ? $data['modified_u_id'] : null;

        $this->_auditor_name            = (isset($data['_auditor_name'])) ? $data['_auditor_name'] : null;
        $this->_consultant_name         = (isset($data['_consultant_name'])) ? $data['_consultant_name'] : null;
        $this->_performed_name          = (isset($data['_performed_name'])) ? $data['_performed_name'] : null;
        $this->_acceptor_name           = (isset($data['_approved_name'])) ? $data['_approved_name'] : null;
        $this->_approved_name           = (isset($data['_approved_name'])) ? $data['_approved_name'] : null;

        
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}