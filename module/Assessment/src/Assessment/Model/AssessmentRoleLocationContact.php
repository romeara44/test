<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentRoleLocationContact
{

    public $arlc_id;
    public $arlc_a_id;
    public $arlc_adr_id;
    public $arlc_ar_id;
    public $arlc_u_id;
    public $arlc_create_u_id;
    public $arlc_update_u_id;
    public $arlc_active;
    public $arlc_create_date;
    public $arlc_update_date;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->arlc_id     = (isset($data['arlc_id'])) ? $data['arlc_id'] : null;
        $this->arlc_a_id     = (isset($data['arlc_a_id'])) ? $data['arlc_a_id'] : null;
        $this->arlc_adr_id     = (isset($data['arlc_adr_id'])) ? $data['arlc_adr_id'] : null;
        $this->arlc_ar_id     = (isset($data['arlc_ar_id'])) ? $data['arlc_ar_id'] : null;
        $this->arlc_u_id     = (isset($data['arlc_u_id'])) ? $data['arlc_u_id'] : null;
        $this->arlc_create_u_id     = (isset($data['arlc_create_u_id'])) ? $data['arlc_create_u_id'] : null;
        $this->arlc_update_u_id     = (isset($data['arlc_update_u_id'])) ? $data['arlc_update_u_id'] : null;
        $this->arlc_active     = (isset($data['arlc_active'])) ? $data['arlc_active'] : null;
        $this->arlc_create_date     = (isset($data['arlc_create_date'])) ? $data['arlc_create_date'] : null;
        $this->arlc_update_date     = (isset($data['arlc_update_date'])) ? $data['arlc_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}