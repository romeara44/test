<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordItemMaster
{
    
    public $audit_record_item_master_id;
    public $audit_inquiry_id;
    public $item_status_id;
    public $audit_record_section_id;
    public $active;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_item_master_id      = (isset($data['audit_record_item_master_id'])) ? $data['audit_record_item_master_id'] : null;
        $this->audit_inquiry_id                 = (isset($data['audit_inquiry_id'])) ? $data['audit_inquiry_id'] : null;
        $this->item_status_id                   = (isset($data['item_status_id'])) ? $data['item_status_id'] : null;
        $this->audit_record_section_id          = (isset($data['audit_record_section_id'])) ? $data['audit_record_section_id'] : null;
        $this->active                           = (isset($data['active'])) ? $data['active'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}