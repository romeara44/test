<?php
namespace Itassetinventory\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ItAssetInventory
{
    public $iai_id;
    public $iai_c_id;
    public $iai_owner_u_id;
    public $iai_consultant_u_id;
    public $iai_location_id;
    public $iai_type_id;
    public $iai_active;
    public $iai_update_u_id;
    public $iai_create_date;
    public $iai_update_date;

    public $_c_name;
    public $_location;
    public $_c_type;
    public $_items;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->iai_id     = (isset($data['iai_id'])) ? $data['iai_id'] : null;

        $this->iai_c_id        = (isset($data['iai_c_id'])) ? $data['iai_c_id'] : null;
        $this->iai_owner_u_id  = (isset($data['iai_owner_u_id'])) ? $data['iai_owner_u_id'] : null;
        $this->iai_consultant_u_id  = (isset($data['iai_consultant_u_id'])) ? $data['iai_consultant_u_id'] : null;
        $this->iai_location_id = (isset($data['iai_location_id'])) ? $data['iai_location_id'] : null;
        $this->iai_type_id     = (isset($data['iai_type_id'])) ? $data['iai_type_id'] : null;
        $this->iai_active      = (isset($data['iai_active'])) ? $data['iai_active'] : null;
        $this->iai_update_u_id = (isset($data['iai_update_u_id'])) ? $data['iai_update_u_id'] : null;
        $this->iai_create_date = (isset($data['iai_create_date'])) ? $data['iai_create_date'] : null;
        $this->iai_update_date = (isset($data['iai_update_date'])) ? $data['iai_update_date'] : null;

        $this->_c_name = (isset($data['_c_name'])) ? $data['_c_name'] : null;
        $this->_location = (isset($data['_location'])) ? $data['_location'] : null;
        $this->_type = (isset($data['_type'])) ? $data['_type'] : null;
        $this->_items = (isset($data['_items'])) ? $data['_items'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl, $isEdit = false, $uId = 0)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'iai_id',
                'required' => $isEdit,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'iai_c_id',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'iai_location_id',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'iai_type_id',
                'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}