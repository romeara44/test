<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwarenessInterface;
use Zend\InputFilter\InputFilterInterface;

class StatusLookup
{
    public $status_id;
    public $status_category_id;
    public $description;
    public $display_description;
    public $active;
    public $display_order;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->status_id            = (isset($data['status_id'])) ? $data['status_id'] : null;
        $this->status_category_id   = (isset($data['status_category_id'])) ? $data['status_category_id'] : null;
        $this->description          = (isset($data['description'])) ? $data['description'] : null;
        $this->display_description  = (isset($data['display_description'])) ? $data['display_description'] : null;
        $this->active               = (isset($data['active'])) ? $data['active'] : null;
        $this->display_order        = (isset($data['display_order'])) ? $data['display_order'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}