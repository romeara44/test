<?php
namespace Traininglog\Model;;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TrainingReport
{

    public $training_report_id;
    public $company_id;
    public $training_url;
    public $url_description;
    public $creation_date;
    public $modified_date;


    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->training_report_id   = (isset($data['training_report_id'])) ? $data['training_report_id'] : null;
        $this->company_id           = (isset($data['company_id']))         ? $data['company_id']         : null;
        $this->training_url         = (isset($data['training_url']))       ? $data['training_url']       : null;
        $this->url_description      = (isset($data['url_description']))    ? $data['url_description']    : null;
        $this->creation_date        = (isset($data['creation_date']))      ? $data['creation_date']      : null;
        $this->modified_date        = (isset($data['modified_date']))      ? $data['modified_date']      : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}