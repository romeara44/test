<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Assessment
{
    const TYPE_SECURITY_RISK = 1;
    const TYPE_PRIVACY_RISK = 2;

    public static $typesNames = array('' => '', 0 => '', self::TYPE_SECURITY_RISK => 'Security Risk Assessment', self::TYPE_PRIVACY_RISK => 'Privacy Risk Assessment');

    const STATUS_INPROGRESS = 10;
    const STATUS_CLOSED = 100;

    public static $statusesNames = array('' => '', self::STATUS_INPROGRESS => 'In Progress', self::STATUS_CLOSED => 'Closed');

    public $a_id;
    public $a_version_index;
    public $a_version_index_item;
    public $a_writable;
    public $a_is_version;
    public $a_parent_a_id;
    public $a_security_a_id;
    public $a_owner_u_id;
    public $a_consultant_u_id;
    public $a_c_id;
    public $a_type;
    public $a_status;
    public $a_step1_finished;
    public $a_step2_finished;
    public $a_step3_finished;
    public $a_step4_finished;
    public $a_step5_finished;
    public $a_all_steps_finished;
    public $a_update_u_id;
    public $a_active;

    public $a_create_date;
    public $a_update_date;
    public $_client_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->a_id     = (isset($data['a_id'])) ? $data['a_id'] : null;

        $this->a_version_index     = (isset($data['a_version_index'])) ? $data['a_version_index'] : null;
        $this->a_version_index_item     = (isset($data['a_version_index_item'])) ? $data['a_version_index_item'] : null;
        $this->a_writable     = (isset($data['a_writable'])) ? $data['a_writable'] : null;
        $this->a_is_version     = (isset($data['a_is_version'])) ? $data['a_is_version'] : null;
        $this->a_parent_a_id     = (isset($data['a_parent_a_id'])) ? $data['a_parent_a_id'] : null;
        $this->a_security_a_id     = (isset($data['a_security_a_id'])) ? $data['a_security_a_id'] : null;

        $this->a_owner_u_id     = (isset($data['a_owner_u_id'])) ? $data['a_owner_u_id'] : null;
        $this->a_c_id     = (isset($data['a_c_id'])) ? $data['a_c_id'] : null;
        $this->a_consultant_u_id     = (isset($data['a_consultant_u_id'])) ? $data['a_consultant_u_id'] : null;
        $this->a_type     = (isset($data['a_type'])) ? $data['a_type'] : null;
        $this->a_status     = (isset($data['a_status'])) ? $data['a_status'] : null;
        $this->a_step1_finished     = (isset($data['a_step1_finished'])) ? $data['a_step1_finished'] : null;
        $this->a_step2_finished     = (isset($data['a_step2_finished'])) ? $data['a_step2_finished'] : null;
        $this->a_step3_finished     = (isset($data['a_step3_finished'])) ? $data['a_step3_finished'] : null;
        $this->a_step4_finished     = (isset($data['a_step4_finished'])) ? $data['a_step4_finished'] : null;
        $this->a_step5_finished     = (isset($data['a_step5_finished'])) ? $data['a_step5_finished'] : null;
        $this->a_all_steps_finished     = (isset($data['a_all_steps_finished'])) ? $data['a_all_steps_finished'] : null;
        $this->a_update_u_id     = (isset($data['a_update_u_id'])) ? $data['a_update_u_id'] : null;
        $this->a_active     = (isset($data['a_active'])) ? $data['a_active'] : null;
        $this->a_create_date     = (isset($data['a_create_date'])) ? $data['a_create_date'] : null;
        $this->a_update_date     = (isset($data['a_update_date'])) ? $data['a_update_date'] : null;

        $this->_client_name     = (isset($data['_client_name'])) ? $data['_client_name'] : null;
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
                'name'     => 'a_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'adr_state_id[]',
                'required' => false,
            )));

            if ($isEdit) {
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'a_c_id',
                    'required' => false,
                )));

                $inputFilter->add($factory->createInput(array(
                    'name'     => 'a_type',
                    'required' => false,
                )));

            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}