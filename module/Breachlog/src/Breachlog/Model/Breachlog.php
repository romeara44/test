<?php
namespace Breachlog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Breachlog
{
    public $bl_id;
    public $bl_c_id;
    public $bl_consultant_u_id;
    public $bl_create_u_id;
    public $bl_update_u_id;
    public $bl_name;
    public $bl_date_of_occurrence;
    public $bl_size;
    public $bl_description;
    public $bl_reportable;
    public $bl_active;
    public $bl_create_date;
    public $bl_update_date;
    public $_client_name;
    public $bl_invest_led_by;
    public $bl_date_invest_start;
    public $bl_date_invest_complete;
    public $bl_initials_approver;
    public $bl_initials;
    public $bl_approver_u_id;
    public $bl_accepter_u_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->bl_id     = (isset($data['bl_id'])) ? $data['bl_id'] : null;
        $this->bl_c_id     = (isset($data['bl_c_id'])) ? $data['bl_c_id'] : null;
        $this->bl_consultant_u_id     = (isset($data['bl_consultant_u_id'])) ? $data['bl_consultant_u_id'] : null;
        $this->bl_create_u_id     = (isset($data['bl_create_u_id'])) ? $data['bl_create_u_id'] : null;
        $this->bl_update_u_id     = (isset($data['bl_update_u_id'])) ? $data['bl_update_u_id'] : null;
        $this->bl_name     = (isset($data['bl_name'])) ? $data['bl_name'] : null;
        $this->bl_date_of_occurrence     = (isset($data['bl_date_of_occurrence'])) ? $data['bl_date_of_occurrence'] : null;
        $this->bl_size     = (isset($data['bl_size'])) ? $data['bl_size'] : null;
        $this->bl_description     = (isset($data['bl_description'])) ? $data['bl_description'] : null;
        $this->bl_reportable     = (isset($data['bl_reportable'])) ? $data['bl_reportable'] : null;
        $this->bl_active     = (isset($data['bl_active'])) ? $data['bl_active'] : null;
        $this->bl_create_date     = (isset($data['bl_create_date'])) ? $data['bl_create_date'] : null;
        $this->bl_update_date     = (isset($data['bl_update_date'])) ? $data['bl_update_date'] : null;
        $this->_client_name     = (isset($data['_client_name'])) ? $data['_client_name'] : null;
        $this->bl_invest_led_by     = (isset($data['bl_invest_led_by'])) ? $data['bl_invest_led_by'] : null;
        $this->bl_date_invest_start     = (isset($data['bl_date_invest_start'])) ? $data['bl_date_invest_start'] : null;
        $this->bl_date_invest_complete     = (isset($data['bl_date_invest_complete'])) ? $data['bl_date_invest_complete'] : null;
        $this->bl_initials_approver     = (isset($data['bl_initials_approver'])) ? $data['bl_initials_approver'] : null;
        $this->bl_initials     = (isset($data['bl_initials'])) ? $data['bl_initials'] : null;
        $this->bl_approver_u_id     = (isset($data['bl_approver_u_id'])) ? $data['bl_approver_u_id'] : null;
        $this->bl_accepter_u_id     = (isset($data['bl_accepter_u_id'])) ? $data['bl_accepter_u_id'] : null;
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
                'name'     => 'bl_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));


            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_date_of_occurrence',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_date_invest_start',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_date_invest_complete',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_initials_approver',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_initials',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_approver_u_id',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                )
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bl_accepter_u_id',
                'required' => false,
                'filters'  => array(
                    array('name' => 'Int'),
                )
            )));


            $authService = new \Zend\Authentication\AuthenticationService();
            $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
            $identity = $authService->getIdentity();

            if ($identity['u_role_id'] == \Admin\Model\User::ROLE_CONSULTANT) {
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'bl_c_id',
                    'required' => true,
                )));
            } else {
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'bl_c_id',
                    'required' => false,
                )));
            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}