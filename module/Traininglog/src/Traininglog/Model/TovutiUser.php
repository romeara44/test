<?php
// module/Traininglog/src/traininglog/Model/TovutiUser.php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class TovutiUser //implements InputFilterAwareInterface
{
    const CUSTOM_FIELD_FIRST_NAME = 19;
    const CUSTOM_FIELD_LAST_NAME = 20;
    const CUSTOM_FIELD_COMPANY_NAME = 22;
    const CUSTOM_FIELD_DEPARTMENT = 24;
    const CUSTOM_FIELD_JOB_TITLE = 25;

    public $tovuti_user_id;
    public $tovuti_id;
    public $first_name;
    public $last_name;
    public $company_name;
    public $department;
    public $job_title;
    public $name;
    public $user_name;
    public $email;
    public $user_group_id;
    public $register_date;
    public $last_visit_date;
    public $require_reset;
    public $status;
    
    //protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tovuti_user_id   = (isset($data['tovuti_user_id'])) ? $data['tovuti_user_id'] : null;
        $this->tovuti_id        = (isset($data['tovuti_id'])) ? $data['tovuti_id'] : null;
        $this->first_name       = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->last_name        = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->company_name     = (isset($data['company_name'])) ? $data['company_name'] : null;
        $this->department       = (isset($data['department'])) ? $data['department'] : null;
        $this->job_title        = (isset($data['job_title']))   ? $data['job_title'] : null;
        $this->name             = (isset($data['name'])) ? $data['name'] : null;
        $this->user_name        = (isset($data['user_name'])) ? $data['user_name'] : null;
        $this->email            = (isset($data['email'])) ? $data['email'] : null;
        $this->user_group_id    = (isset($data['user_group_id'])) ? $data['user_group_id'] : null;
        $this->register_date    = (isset($data['register_date'])) && $data['register_date'] != '0000-00-00 00:00:00' ? $data['register_date'] : null;
        $this->last_visit_date  = (isset($data['last_visit_date'])) && $data['last_visit_date'] != '0000-00-00 00:00:00' ? $data['last_visit_date'] : null;
        $this->require_reset    = (isset($data['require_reset'])) ? $data['require_reset'] : false;
        $this->status           = (isset($data['status'])) ? $data['status'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    // Add content to these methods:
    // public function setInputFilter(InputFilterInterface $inputFilter)
    // {
    //     throw new \Exception("Not used");
    // }

    // public function getInputFilter($sl, $isEdit = false)
    // {
    //     if (!$this->inputFilter) {
    //         $inputFilter = new InputFilter();
    //         $factory     = new InputFactory();

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => 'tl_company_id',
    //             'required' => true,
    //             'filters'  => array(
    //                 array('name' => 'Int'),
    //             ),
    //         )));

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => 'tl_id',
    //             'required' => false,
    //             'filters'  => array(
    //                 array('name' => 'Int'),
    //             ),
    //         )));

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => 'tl_title',
    //             'required' => true,
    //             'filters'  => array(
    //                 array('name' => 'StripTags'),
    //                 array('name' => 'StringTrim'),
    //             ),
    //             'validators' => array(
    //                 array(
    //                     'name'    => 'StringLength',
    //                     'options' => array(
    //                         'encoding' => 'UTF-8',
    //                         'min'      => 1,
    //                         'max'      => 100,
    //                     ),
    //                 ),
    //             ),
    //         )));

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => 'tl_tlt_id',
    //             'required' => true,
    //             'filters'  => array(
    //                 array('name' => 'Int'),
    //             ),
    //         )));

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => '_tl_type_name',
    //             'required' => false,
    //             'filters'  => array(
    //                 array('name' => 'StripTags'),
    //                 array('name' => 'StringTrim'),
    //             ),
    //             'validators' => array(
    //                 array(
    //                     'name'    => 'StringLength',
    //                     'options' => array(
    //                         'encoding' => 'UTF-8',
    //                         'min'      => 1,
    //                         'max'      => 100,
    //                     ),
    //                 ),
    //             ),
    //         )));

    //         $inputFilter->add($factory->createInput(array(
    //             'name'     => '_tl_eml_items',
    //             'required' => false,
    //         )));

    //         $this->inputFilter = $inputFilter;
    //     }

    //     return $this->inputFilter;
    // }

}