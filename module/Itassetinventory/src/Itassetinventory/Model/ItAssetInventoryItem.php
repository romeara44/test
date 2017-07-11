<?php
namespace Itassetinventory\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ItAssetInventoryItem
{

    public $iaii_id;
    public $iaii_iai_id;
    public $iaii_iaiit_id;
    public $iaii_name;
    public $iaii_model;
    public $iaii_description;
    public $iaii_create_u_id;
    public $iaii_update_u_id;
    public $iaii_active;
    public $iaii_create_date;
    public $iaii_update_date;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->iaii_id     = (isset($data['iaii_id'])) ? $data['iaii_id'] : null;
        $this->iaii_iai_id     = (isset($data['iaii_iai_id'])) ? $data['iaii_iai_id'] : null;
        $this->iaii_iaiit_id     = (isset($data['iaii_iaiit_id'])) ? $data['iaii_iaiit_id'] : null;
        $this->iaii_name     = (isset($data['iaii_name'])) ? $data['iaii_name'] : null;
        $this->iaii_model     = (isset($data['iaii_model'])) ? $data['iaii_model'] : null;
        $this->iaii_description     = (isset($data['iaii_description'])) ? $data['iaii_description'] : null;
        $this->iaii_create_u_id     = (isset($data['iaii_create_u_id'])) ? $data['iaii_create_u_id'] : null;
        $this->iaii_update_u_id     = (isset($data['iaii_update_u_id'])) ? $data['iaii_update_u_id'] : null;
        $this->iaii_active     = (isset($data['iaii_active'])) ? $data['iaii_active'] : null;
        $this->iaii_create_date     = (isset($data['iaii_create_date'])) ? $data['iaii_create_date'] : null;
        $this->iaii_update_date     = (isset($data['iaii_update_date'])) ? $data['iaii_update_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}