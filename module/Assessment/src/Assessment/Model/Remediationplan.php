<?php
namespace Assessment\Model;

class Remediationplan
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_SIGNED_OFF = 30;
    const STATUS_CLOSED = 40;

    public static $statusesNames = array(self::STATUS_NEW => 'New', self::STATUS_OPEN => 'Open', self::STATUS_SIGNED_OFF => 'Signed Off', self::STATUS_CLOSED => 'Closed');

    public $rp_id;
    public $rp_version_index;
    public $rp_version_index_item;
    public $rp_writable;
    public $rp_type;
    public $rp_a_id;
    public $rp_c_id;
    public $rp_consultant_u_id;
    public $rp_approver_u_id;
    public $rp_performed_u_id;
    public $rp_create_u_id;
    public $rp_update_u_id;
    public $rp_parent_rp_id;
    public $rp_is_version;
    public $rp_status;
    public $rp_active;
    public $rp_incident_date;
    public $rp_remediation_date;
    public $rp_initials;
    public $rp_initials_approver;
    public $rp_create_date;
    public $_rp_incident_date_formatted;
    public $_rp_remediation_date_formatted;
    public $rp_accepter_u_id;
    public $rp_approved_date;
    public $rp_accepted_date;
    public $_rp_approved_date_formatted;
    public $_rp_accepted_date_formatted;
    
    public $rp_security_rp_id;
    public $_client_name;
    public $_approver_name;
    public $_accepter_name;
    public $_consultant_name;
    public $_performed_name;
    public $_u_id;
    public $_u_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->rp_id     = (isset($data['rp_id'])) ? $data['rp_id'] : null;
        $this->rp_version_index     = (isset($data['rp_version_index'])) ? $data['rp_version_index'] : null;
        $this->rp_version_index_item     = (isset($data['rp_version_index_item'])) ? $data['rp_version_index_item'] : null;
        $this->rp_writable     = (isset($data['rp_writable'])) ? $data['rp_writable'] : null;
        $this->rp_type     = (isset($data['rp_type'])) ? $data['rp_type'] : null;
        $this->rp_a_id     = (isset($data['rp_a_id'])) ? $data['rp_a_id'] : null;
        $this->rp_c_id     = (isset($data['rp_c_id'])) ? $data['rp_c_id'] : null;
        $this->rp_consultant_u_id     = (isset($data['rp_consultant_u_id'])) ? $data['rp_consultant_u_id'] : null;
        $this->rp_approver_u_id     = (isset($data['rp_approver_u_id'])) ? $data['rp_approver_u_id'] : null;
        $this->rp_performed_u_id     = (isset($data['rp_performed_u_id'])) ? $data['rp_performed_u_id'] : null;
        $this->rp_create_u_id     = (isset($data['rp_create_u_id'])) ? $data['rp_create_u_id'] : null;
        $this->rp_update_u_id     = (isset($data['rp_update_u_id'])) ? $data['rp_update_u_id'] : null;
        $this->rp_accepter_u_id     = (isset($data['rp_accepter_u_id'])) ? $data['rp_accepter_u_id'] : null;
        $this->rp_parent_rp_id     = (isset($data['rp_parent_rp_id'])) ? $data['rp_parent_rp_id'] : null;
        $this->rp_is_version     = (isset($data['rp_is_version'])) ? $data['rp_is_version'] : null;
        $this->rp_status     = (isset($data['rp_status'])) ? $data['rp_status'] : null;
        $this->rp_active     = (isset($data['rp_active'])) ? $data['rp_active'] : null;
        $this->rp_incident_date     = (isset($data['rp_incident_date'])) ? $data['rp_incident_date'] : null;
        $this->rp_remediation_date     = (isset($data['rp_remediation_date'])) ? $data['rp_remediation_date'] : null;
        $this->rp_approved_date     = (isset($data['rp_approved_date'])) ? $data['rp_approved_date'] : null;
        $this->rp_accepted_date     = (isset($data['rp_accepted_date'])) ? $data['rp_accepted_date'] : null;
        $this->rp_initials     = (isset($data['rp_initials'])) ? $data['rp_initials'] : null;
        $this->rp_initials_approver     = (isset($data['rp_initials_approver'])) ? $data['rp_initials_approver'] : null;
        $this->rp_create_date     = (isset($data['rp_create_date'])) ? $data['rp_create_date'] : null;
        $this->rp_security_rp_id     = (isset($data['rp_security_rp_id'])) ? $data['rp_security_rp_id'] : null;
        $this->_client_name     = (isset($data['_client_name'])) ? $data['_client_name'] : null;
        $this->_approver_name     = (isset($data['_approver_name'])) ? $data['_approver_name'] : null;
        $this->_accepter_name     = (isset($data['_accepter_name'])) ? $data['_accepter_name'] : null;
        $this->_consultant_name     = (isset($data['_consultant_name'])) ? $data['_consultant_name'] : null;
        $this->_performed_name     = (isset($data['_performed_name'])) ? $data['_performed_name'] : null;
        $this->_rp_incident_date_formatted     = (isset($data['_rp_incident_date_formatted'])) ? $data['_rp_incident_date_formatted'] : null;
        $this->_rp_remediation_date_formatted     = (isset($data['_rp_remediation_date_formatted'])) ? $data['_rp_remediation_date_formatted'] : null;
        $this->_rp_approved_date_formatted     = (isset($data['_rp_approved_date_formatted'])) ? $data['_rp_approved_date_formatted'] : null;
        $this->_rp_accepted_date_formatted     = (isset($data['_rp_accepted_date_formatted'])) ? $data['_rp_accepted_date_formatted'] : null;
        $this->_u_id     = (isset($data['_u_id'])) ? $data['_u_id'] : null;
        $this->_u_name     = (isset($data['_u_name'])) ? $data['_u_name'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}