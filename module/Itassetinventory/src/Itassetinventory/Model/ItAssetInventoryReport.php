<?php
namespace Itassetinventory\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ItAssetInventoryReport
{

    public $iair_id;
    public $iair_iai_id;
    public $iair_iaiit_id;
    public $iair_f_id;
    public $iair_create_u_id;
    public $iair_update_u_id;
    public $iair_active;
    public $iair_create_date;
    public $iair_update_date;

    public $_filename;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->iair_id     = (isset($data['iair_id'])) ? $data['iair_id'] : null;
        $this->iair_iai_id     = (isset($data['iair_iai_id'])) ? $data['iair_iai_id'] : null;
        $this->iair_iaiit_id     = (isset($data['iair_iaiit_id'])) ? $data['iair_iaiit_id'] : null;
        $this->iair_f_id     = (isset($data['iair_f_id'])) ? $data['iair_f_id'] : null;
        $this->iair_create_u_id     = (isset($data['iair_create_u_id'])) ? $data['iair_create_u_id'] : null;
        $this->iair_update_u_id     = (isset($data['iair_update_u_id'])) ? $data['iair_update_u_id'] : null;
        $this->iair_active     = (isset($data['iair_active'])) ? $data['iair_active'] : null;
        $this->iair_create_date     = (isset($data['iair_create_date'])) ? $data['iair_create_date'] : null;
        $this->iair_update_date     = (isset($data['iair_update_date'])) ? $data['iair_update_date'] : null;

        $this->_filename     = (isset($data['_filename'])) ? $data['_filename'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}