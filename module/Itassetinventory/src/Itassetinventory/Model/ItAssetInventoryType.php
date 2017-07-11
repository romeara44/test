<?php
namespace Itassetinventory\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ItAssetInventoryType
{
    public $iait_id;
    public $iait_name;
    public $iait_order;
    public $iait_active;
    public $iait_create_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->iait_id          = (isset($data['iait_id'])) ? $data['iait_id'] : null;
        $this->iait_name        = (isset($data['iait_name'])) ? $data['iait_name'] : null;
        $this->iait_order       = (isset($data['iait_order'])) ? $data['iait_order'] : null;
        $this->iait_active      = (isset($data['iait_active'])) ? $data['iait_active'] : null;
        $this->iait_create_date = (isset($data['iait_create_date'])) ? $data['iait_create_date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}