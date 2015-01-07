<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AssessmentInventoryLocationItem
{

    public $aili_id;
    public $aili_a_id;
    public $aili_adr_id;
    public $aili_ai_id;
    public $aili_name;
    public $aili_model;
    public $aili_description;
    public $aili_create_u_id;
    public $aili_update_u_id;
    public $aili_active;
    public $aili_create_date;
    public $aili_update_date;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->aili_id     = (isset($data['aili_id'])) ? $data['aili_id'] : null;
        $this->aili_a_id     = (isset($data['aili_a_id'])) ? $data['aili_a_id'] : null;
        $this->aili_adr_id     = (isset($data['aili_adr_id'])) ? $data['aili_adr_id'] : null;
        $this->aili_ai_id     = (isset($data['aili_ai_id'])) ? $data['aili_ai_id'] : null;
        $this->aili_name     = (isset($data['aili_name'])) ? $data['aili_name'] : null;
        $this->aili_model     = (isset($data['aili_model'])) ? $data['aili_model'] : null;
        $this->aili_description     = (isset($data['aili_description'])) ? $data['aili_description'] : null;
        $this->aili_create_u_id     = (isset($data['aili_create_u_id'])) ? $data['aili_create_u_id'] : null;
        $this->aili_update_u_id     = (isset($data['aili_update_u_id'])) ? $data['aili_update_u_id'] : null;
        $this->aili_active     = (isset($data['aili_active'])) ? $data['aili_active'] : null;
        $this->aili_create_date     = (isset($data['aili_create_date'])) ? $data['aili_create_date'] : null;
        $this->aili_update_date     = (isset($data['aili_update_date'])) ? $data['aili_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}