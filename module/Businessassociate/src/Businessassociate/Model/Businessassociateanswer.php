<?php
namespace Businessassociate\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Businessassociateanswer
{
    public $baa_id;
    public $baa_ba_id;
    public $baa_baq_id;
    public $baa_u_id;
    public $baa_value;
    public $baa_active;
    public $baa_create_date;

    public $_baq_title;
    public $_note_text;
    public $_files;
    public $note_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->baa_id     = (isset($data['baa_id'])) ? $data['baa_id'] : null;
        $this->baa_ba_id     = (isset($data['baa_ba_id'])) ? $data['baa_ba_id'] : null;
        $this->baa_baq_id     = (isset($data['baa_baq_id'])) ? $data['baa_baq_id'] : null;
        $this->baa_u_id     = (isset($data['baa_u_id'])) ? $data['baa_u_id'] : null;
        $this->baa_value     = (isset($data['baa_value'])) ? $data['baa_value'] : null;
        $this->baa_active     = (isset($data['baa_active'])) ? $data['baa_active'] : null;
        $this->baa_create_date     = (isset($data['baa_create_date'])) ? $data['baa_create_date'] : null;

        $this->_baq_title     = (isset($data['_baq_title'])) ? $data['_baq_title'] : null;
        $this->_note_text     = (isset($data['_note_text'])) ? $data['_note_text'] : null;
        $this->_files     = (isset($data['_files'])) ? $data['_files'] : null;
        $this->note_id     = (isset($data['note_id'])) ? $data['note_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}