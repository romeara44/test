<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentInventoryLocationReport
{

    public $ailr_id;
    public $ailr_a_id;
    public $ailr_adr_id;
    public $ailr_ai_id;
    public $ailr_f_id;
    public $ailr_create_u_id;
    public $ailr_update_u_id;
    public $ailr_active;
    public $ailr_create_date;
    public $ailr_update_date;

    public $_filename;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->ailr_id     = (isset($data['ailr_id'])) ? $data['ailr_id'] : null;
        $this->ailr_a_id     = (isset($data['ailr_a_id'])) ? $data['ailr_a_id'] : null;
        $this->ailr_adr_id     = (isset($data['ailr_adr_id'])) ? $data['ailr_adr_id'] : null;
        $this->ailr_ai_id     = (isset($data['ailr_ai_id'])) ? $data['ailr_ai_id'] : null;
        $this->ailr_f_id     = (isset($data['ailr_f_id'])) ? $data['ailr_f_id'] : null;
        $this->ailr_create_u_id     = (isset($data['ailr_create_u_id'])) ? $data['ailr_create_u_id'] : null;
        $this->ailr_update_u_id     = (isset($data['ailr_update_u_id'])) ? $data['ailr_update_u_id'] : null;
        $this->ailr_active     = (isset($data['ailr_active'])) ? $data['ailr_active'] : null;
        $this->ailr_create_date     = (isset($data['ailr_create_date'])) ? $data['ailr_create_date'] : null;
        $this->ailr_update_date     = (isset($data['ailr_update_date'])) ? $data['ailr_update_date'] : null;

        $this->_filename     = (isset($data['_filename'])) ? $data['_filename'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}