<?php
namespace Breachlog\Model;

class Breachremediationplan
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_SIGNED_OFF = 30;
    const STATUS_CLOSED = 40;

    public static $statusesNames = array(self::STATUS_NEW => 'New', self::STATUS_OPEN => 'Open', self::STATUS_SIGNED_OFF => 'Signed Off', self::STATUS_CLOSED => 'Closed');

    public $brp_id;
    public $brp_version_index;
    public $brp_version_index_item;
    public $brp_writable;
    public $brp_bl_id;
    public $brp_c_id;
    public $brp_consultant_u_id;
    public $brp_performed_u_id;
    public $brp_approver_u_id;
    public $brp_create_u_id;
    public $brp_update_u_id;
    public $brp_parent_brp_id;
    public $brp_is_version;
    public $brp_status;
    public $brp_incident_date;
    public $brp_remediation_date;
    public $brp_initials;
    public $brp_active;
    public $brp_initials_approver;
    public $_brp_incident_date_formatted;
    public $_brp_remediation_date_formatted;

    public $brp_create_date;
    public $_client_name;
    public $_approver_name;
    public $_consultant_name;
    public $_performed_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->brp_id     = (isset($data['brp_id'])) ? $data['brp_id'] : null;
        $this->brp_version_index     = (isset($data['brp_version_index'])) ? $data['brp_version_index'] : null;
        $this->brp_version_index_item     = (isset($data['brp_version_index_item'])) ? $data['brp_version_index_item'] : null;
        $this->brp_writable     = (isset($data['brp_writable'])) ? $data['brp_writable'] : null;
        $this->brp_bl_id     = (isset($data['brp_bl_id'])) ? $data['brp_bl_id'] : null;
        $this->brp_c_id     = (isset($data['brp_c_id'])) ? $data['brp_c_id'] : null;
        $this->brp_consultant_u_id     = (isset($data['brp_consultant_u_id'])) ? $data['brp_consultant_u_id'] : null;
        $this->brp_performed_u_id     = (isset($data['brp_performed_u_id'])) ? $data['brp_performed_u_id'] : null;
        $this->brp_approver_u_id     = (isset($data['brp_approver_u_id'])) ? $data['brp_approver_u_id'] : null;
        $this->brp_create_u_id     = (isset($data['brp_create_u_id'])) ? $data['brp_create_u_id'] : null;
        $this->brp_update_u_id     = (isset($data['brp_update_u_id'])) ? $data['brp_update_u_id'] : null;
        $this->brp_parent_brp_id     = (isset($data['brp_parent_brp_id'])) ? $data['brp_parent_brp_id'] : null;
        $this->brp_is_version     = (isset($data['brp_is_version'])) ? $data['brp_is_version'] : null;
        $this->brp_status     = (isset($data['brp_status'])) ? $data['brp_status'] : null;
        $this->brp_incident_date     = (isset($data['brp_incident_date'])) ? $data['brp_incident_date'] : null;
        $this->brp_remediation_date     = (isset($data['brp_remediation_date'])) ? $data['brp_remediation_date'] : null;
        $this->brp_initials     = (isset($data['brp_initials'])) ? $data['brp_initials'] : null;
        $this->brp_active     = (isset($data['brp_active'])) ? $data['brp_active'] : null;
        $this->brp_initials_approver     = (isset($data['brp_initials_approver'])) ? $data['brp_initials_approver'] : null;
        $this->brp_create_date     = (isset($data['brp_create_date'])) ? $data['brp_create_date'] : null;
        $this->_client_name     = (isset($data['_client_name'])) ? $data['_client_name'] : null;
        $this->_approver_name     = (isset($data['_approver_name'])) ? $data['_approver_name'] : null;
        $this->_consultant_name     = (isset($data['_consultant_name'])) ? $data['_consultant_name'] : null;
        $this->_performed_name     = (isset($data['_performed_name'])) ? $data['_performed_name'] : null;
        $this->_brp_incident_date_formatted     = (isset($data['_brp_incident_date_formatted'])) ? $data['_brp_incident_date_formatted'] : null;
        $this->_brp_remediation_date_formatted     = (isset($data['_brp_remediation_date_formatted'])) ? $data['_brp_remediation_date_formatted'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}