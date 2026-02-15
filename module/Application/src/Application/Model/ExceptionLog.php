<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ExceptionLog
{

    public $excepton_log_id;
    public $user_id;
    public $user_name;
    public $error_location;
    public $error_message;
    public $stack_trace;
    public $module_name_display;
    public $creation_user;
    public $modified_user;
    public $creation_date;
    public $modified_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->excepton_log_id      = (isset($data['excepton_log_id'])) ? $data['excepton_log_id'] : null;
        $this->user_id              = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->user_name            = (isset($data['user_name'])) ? $data['user_name'] : null;
        $this->error_location       = (isset($data['error_location'])) ? $data['error_location'] : null;
        $this->error_message        = (isset($data['error_message'])) ? $data['error_message'] : null;
        $this->stack_trace          = (isset($data['stack_trace'])) ? $data['stack_trace'] : null;
        $this->module_name_display  = (isset($data['module_name_display'])) ? $data['module_name_display'] : null;
        $this->creation_user        = (isset($data['creation_user'])) ? $data['creation_user'] : null;
        $this->modified_user        = (isset($data['modified_user'])) ? $data['modified_user'] : null;
        $this->creation_date        = (isset($data['creation_date'])) ? $data['creation_date'] : null;
        $this->modified_date        = (isset($data['modified_date'])) ? $data['modified_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}