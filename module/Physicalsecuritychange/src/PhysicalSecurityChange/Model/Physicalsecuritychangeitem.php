<?php
namespace Physicalsecuritychange\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Physicalsecuritychangeitem
{
    public $psci_id;
    public $psci_psc_id;
    public $psci_date;
    public $psci_identification;
    public $psci_reason;
    public $psci_person;
    public $psci_individual;

    public $psci_comments;
    public $psci_create_u_id;
    public $psci_create_date;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->psci_id           = (isset($data['psci_id']))           ? $data['psci_id']           : null;
        $this->psci_psc_id           = (isset($data['psci_psc_id']))           ? $data['psci_psc_id']           : null;
        $this->psci_date               = (isset($data['psci_date']))               ? $data['psci_date']               : null;
        $this->psci_identification            = (isset($data['psci_identification']))            ? $data['psci_identification']            : null;
        $this->psci_reason           = (isset($data['psci_reason']))           ? $data['psci_reason']           : null;
        $this->psci_person      = (isset($data['psci_person']))      ? $data['psci_person']      : null;
        $this->psci_individual      = (isset($data['psci_individual']))      ? $data['psci_individual']      : null;

        $this->psci_comments      = (isset($data['psci_comments']))      ? $data['psci_comments']      : null;
        $this->psci_create_u_id      = (isset($data['psci_create_u_id']))      ? $data['psci_create_u_id']      : null;
        $this->psci_create_date      = (isset($data['psci_create_date']))      ? $data['psci_create_date']      : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}