<?php
namespace Audit\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AuditRecordItemComment
{
    
    public $audit_record_comment_id;
    public $audit_record_item_id;
    public $comment;
    public $comment_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->audit_record_comment_id  = (isset($data['audit_record_comment_id'])) ? $data['audit_record_comment_id'] : null;
        $this->audit_record_item_id     = (isset($data['audit_record_item_id'])) ? $data['audit_record_item_id'] : null;
        $this->comment                  = (isset($data['comment'])) ? $data['comment'] : null;
        $this->comment_date             = (isset($data['comment_date'])) ? $data['comment_date'] : null;
        
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}