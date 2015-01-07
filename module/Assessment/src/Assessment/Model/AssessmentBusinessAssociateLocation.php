<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentBusinessAssociateLocation
{

    public $abal_id;
    public $abal_a_id;
    public $abal_adr_id;
    public $abal_ba_id;
    public $abal_create_u_id;
    public $abal_update_u_id;
    public $abal_active;
    public $abal_create_date;
    public $abal_update_date;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->abal_id     = (isset($data['abal_id'])) ? $data['abal_id'] : null;
        $this->abal_a_id     = (isset($data['abal_a_id'])) ? $data['abal_a_id'] : null;
        $this->abal_adr_id     = (isset($data['abal_adr_id'])) ? $data['abal_adr_id'] : null;
        $this->abal_ba_id     = (isset($data['abal_ba_id'])) ? $data['abal_ba_id'] : null;
        $this->abal_create_u_id     = (isset($data['abal_create_u_id'])) ? $data['abal_create_u_id'] : null;
        $this->abal_update_u_id     = (isset($data['abal_update_u_id'])) ? $data['abal_update_u_id'] : null;
        $this->abal_active     = (isset($data['abal_active'])) ? $data['abal_active'] : null;
        $this->abal_create_date     = (isset($data['abal_create_date'])) ? $data['abal_create_date'] : null;
        $this->abal_update_date     = (isset($data['abal_update_date'])) ? $data['abal_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}