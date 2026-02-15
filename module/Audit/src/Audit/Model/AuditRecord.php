<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecord
{
    const TYPE_HIPAA = 1;
    const TYPE_SOC = 2;
    const QUEST_TYPE_SECURITY = 3;
    const QUEST_TYPE_PRIVACY = 4;
    const QUEST_TYPE_BREACH = 5;

    const AUDIT_STATUS = 5;

    public static $typesNames = array('' => '', 0 => '', self::TYPE_HIPAA => 'HIPAA Audit', self::TYPE_SOC => 'SOC Audit');
    public static $questTypeNames = array(self::QUEST_TYPE_SECURITY => 'Security', self::QUEST_TYPE_PRIVACY => 'Privacy', self::QUEST_TYPE_BREACH => 'Breach');
    
    public $audit_record_id;
    public $rp_id;
    public $company_id;
    public $auditor_u_id;
    public $date_audited;
    public $audit_status_id;
    public $reviewed_u_id;
    public $reviewed_initials;
    public $reviewed_date;
    public $auditor_approve_u_id;
    public $auditor_approve_initials;
    public $auditor_approve_date;
    public $is_locked;
    public $creation_date;
    
    public $_audit_status_name;
    public $_assement_type;
    public $_location_name;
    public $_user_first_name;
    public $_user_last_name;
    public $_u_name;
    public $_u_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_id          = (isset($data['audit_record_id'])) ? $data['audit_record_id'] : null;
        $this->rp_id                    = (isset($data['rp_id'])) ? $data['rp_id'] : null;
        $this->company_id               = (isset($data['company_id'])) ? $data['company_id'] : null;
        $this->auditor_u_id             = (isset($data['auditor_u_id'])) ? $data['auditor_u_id'] : null;
        $this->date_audited             = (isset($data['date_audited'])) ? $data['date_audited'] : null;
        $this->audit_status_id          = (isset($data['audit_status_id'])) ? $data['audit_status_id'] : null;
        $this->reviewed_u_id            = (isset($data['reviewed_u_id'])) ? $data['reviewed_u_id'] : null;
        $this->reviewed_initials        = (isset($data['reviewed_initials'])) ? $data['reviewed_initials'] : null;
        $this->reviewed_date            = (isset($data['reviewed_date'])) ? $data['reviewed_date'] : null;
        $this->auditor_approve_u_id     = (isset($data['auditor_approve_u_id'])) ? $data['auditor_approve_u_id'] : null;
        $this->auditor_approve_initials = (isset($data['auditor_approve_initials'])) ? $data['auditor_approve_initials'] : null;
        $this->auditor_approve_date     = (isset($data['auditor_approve_date'])) ? $data['auditor_approve_date'] : null;
        $this->is_locked                = (isset($data['is_locked'])) ? $data['is_locked'] : null;
        $this->creation_date            = (isset($data['creation_date'])) ? $data['creation_date'] : null;
        
        $this->_audit_status_name       = (isset($data['_audit_status_name'])) ? $data['_audit_status_name'] : null;
        $this->_assement_type           = (isset($data['_assement_type'])) ? $data['_assement_type'] : null;
        $this->_location_name           = (isset($data['_location_name'])) ? $data['_location_name'] : null;
        $this->_user_first_name         = (isset($data['_user_first_name'])) ? $data['_user_first_name'] : null;
        $this->_user_last_name          = (isset($data['_user_last_name'])) ? $data['_user_last_name'] : null;
        $this->_u_name                  = (isset($data['_u_name'])) ? $data['_u_name'] : null;
        $this->_u_id                    = (isset($data['_u_id'])) ? $data['_u_id'] : null;
        $this->_remediationPlanIndex    = (isset($data['remediationPlanObject'])) ? $data['remediationPlanObject'] : null;

        //audit_inquiry Table
        $this->_audit_record_types      = (isset($data['_audit_record_types'])) ? $data['_audit_record_types'] : null;
    }

    public function exchangeObject($data)
    {
        $this->audit_record_id          = (isset($data->audit_record_id)) ? $data->audit_record_id : null;
        $this->rp_id                    = (isset($data->rp_id)) ? $data->rp_id : null;
        $this->company_id               = (isset($data->company_id)) ? $data->company_id : null;
        $this->auditor_u_id             = (isset($data->auditor_u_id)) ? $data->auditor_u_id : null;
        $this->date_audited             = (isset($data->date_audited)) ? $data->date_audited : null;

        $this->audit_status_id          = (isset($data->audit_status_id)) ? $data->audit_status_id : null;
        $this->reviewed_u_id            = (isset($data->reviewed_u_id)) ? $data->reviewed_u_id : null;
        $this->reviewed_initials        = (isset($data->reviewed_initials)) ? $data->reviewed_initials : null;
        $this->reviewed_date            = (isset($data->reviewed_date)) ? $data->reviewed_date : null;
        $this->auditor_approve_u_id     = (isset($data->auditor_approve_u_id)) ? $data->auditor_approve_u_id : null;
        $this->auditor_approve_initials = (isset($data->auditor_approve_initials)) ? $data->auditor_approve_initials : null;
        $this->auditor_approve_date     = (isset($data->auditor_approve_date)) ? $data->auditor_approve_date : null;
        $this->is_locked                = (isset($data->is_locked)) ? $data->is_locked : null;
        $this->creation_date            = (isset($data->creation_date)) ? $data->creation_date : null;
        
        $this->_audit_status_name       = (isset($data->_audit_status_name)) ? $data->_audit_status_name : null;
        $this->_assement_type           = (isset($data->_assement_type)) ? $data->_assement_type : null;
        $this->_location_name           = (isset($data->_location_name)) ? $data->_location_name : null;
        $this->_user_first_name         = (isset($data->_user_first_name)) ? $data->_user_first_name : null;
        $this->_user_last_name          = (isset($data->_user_last_name)) ? $data->_user_last_name : null;
        $this->_u_name                  = (isset($data->_u_name)) ? $data->_u_name : null;
        $this->_u_id                    = (isset($data->_u_id)) ? $data->_u_id : null;
        $this->_remediationPlanIndex    = (isset($data->remediationPlanObject)) ? $data->remediationPlanObject : null;

        //audit_inquiry Table
        $this->_audit_record_types      = (isset($data->_audit_record_types)) ? $data->_audit_record_types : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}