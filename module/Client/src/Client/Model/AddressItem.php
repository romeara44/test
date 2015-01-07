<?php
namespace Client\Model;

class AddressItem
{
    const COMPANY_TYPE = 1;
    const ASSESSMENT_TYPE = 2;

    public $cadr_id;
    public $cadr_type;
    public $cadr_c_id;
    public $cadr_adr_id;
    public $cadr_active;

    public function exchangeArray($data)
    {
        $this->cadr_type     = (isset($data['cadr_type'])) ? $data['cadr_type'] : null;
        $this->cadr_c_id     = (isset($data['cadr_c_id'])) ? $data['cadr_c_id'] : null;
        $this->cadr_adr_id     = (isset($data['cadr_adr_id'])) ? $data['cadr_adr_id'] : null;
        $this->cadr_active     = (isset($data['cadr_active'])) ? $data['cadr_active'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }


}