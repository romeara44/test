<?php
namespace Note\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Note
{
    const NOTE_COMPANY = 1;
    const NOTE_BUSINESSASSOCIATE = 2;
    const NOTE_BUSINESSASSOCIATE_ANSWER = 3;
    const NOTE_BRP = 4;  //Breach Remediation Plan
    const NOTE_BRPA = 5; //Breach Remediation Plan Action
    const NOTE_AILI = 6;
    const NOTE_ABAL = 7;  //Asset Business Associate List Item
    const NOTE_ASSESSMENT_ANSWER = 8;  //Assessment Item Question Answer Note
    const NOTE_CONTACT = 9;  //Client Note
    const NOTE_RP = 10;  //Remediation Plan
    const NOTE_RPA = 11;  //Remediation Plan Action
    const NOTE_BL = 12;  //Breach Log
    const NOTE_TLT = 13;  // Note TrainingLog Table
    const NOTE_TLC = 14;  //TrainingLog Ccomment
    const NOTE_SRM = 15;  //Security Reminder
    const NOTE_SRC = 16; //Security Reminder Controller
    const NOTE_IAIR = 17;  //IT Asset Inventory R?
    const NOTE_IAIN = 18;  //IT Asset Inventory N?
    const NOTE_PSC = 19;  //Physical Security Change
    const NOTE_DR = 20;  //Disclosure Record
    const NOTE_AR = 21; //Accounting Request
    const NOTE_AUDIT_ITEM = 22;  //Audit Item

    public $note_id;
    public $note_text;
    public $note_u_id;
    public $note_item_type;
    public $note_item_id;
    public $note_subitem_id;
    public $note_active;
    public $note_create_date;
    public $note_encrypted;
    public $_note_create_date_format;
    public $_username;
    public $_files;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->note_id                  = (isset($data['note_id'])) ? $data['note_id'] : null;
        $this->note_text                = (isset($data['note_text'])) ? $data['note_text'] : null;
        $this->note_u_id                = (isset($data['note_u_id'])) ? $data['note_u_id'] : null;
        $this->note_item_type           = (isset($data['note_item_type'])) ? $data['note_item_type'] : null;
        $this->note_item_id             = (isset($data['note_item_id'])) ? $data['note_item_id'] : null;
        $this->note_subitem_id          = (isset($data['note_subitem_id'])) ? $data['note_subitem_id'] : null;
        $this->note_active              = (isset($data['note_active'])) ? $data['note_active'] : null;
        $this->note_create_date         = (isset($data['note_create_date'])) ? $data['note_create_date'] : null;
        $this->note_encrypted           = (isset($data['note_encrypted'])) ? $data['note_encrypted'] : null;
        $this->_username                = (isset($data['_username'])) ? $data['_username'] : null;
        $this->_files                   = (isset($data['_files'])) ? $data['_files'] : null;
        $this->_note_create_date_format = (isset($data['_note_create_date_format'])) ? $data['_note_create_date_format'] : null;
    }

    public function exchangeObject($data)
    {
        $this->note_id                  = (isset($data->note_id)) ? $data->note_id : null;
        $this->note_text                = (isset($data->note_text)) ? $data->note_text : null;
        $this->note_u_id                = (isset($data->note_u_id)) ? $data->note_u_id : null;
        $this->note_item_type           = (isset($data->note_item_type)) ? $data->note_item_type : null;
        $this->note_item_id             = (isset($data->note_item_id)) ? $data->note_item_id : null;
        $this->note_subitem_id          = (isset($data->note_subitem_id)) ? $data->note_subitem_id : null;
        $this->note_active              = (isset($data->note_active)) ? $data->note_active : null;
        $this->note_create_date         = (isset($data->note_create_date)) ? $data->note_create_date : null;
        $this->note_encrypted           = (isset($data->note_encrypted)) ? $data->note_encrypted : null;
        $this->_username                = (isset($data->_username)) ? $data->_username : null;
        $this->_files                   = (isset($data->_files)) ? $data->_files : null;
        $this->_note_create_date_format = (isset($data->_note_create_date_format)) ? $data->_note_create_date_format : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'note_text',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                )
            )));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }


}