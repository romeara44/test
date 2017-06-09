<?php
namespace Businessassociate\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Businessassociate
{
    public $ba_id;
    public $ba_contact_u_id;
    public $ba_consultant_u_id;
    public $ba_create_u_id;
    public $ba_update_u_id;
    public $ba_c_id;
    public $ba_name;
    public $ba_office_phone;
    public $ba_office_phone_inner;
    public $ba_direct_phone;
    public $ba_direct_phone_inner;
    public $ba_other_phone;
    public $ba_other_phone_inner;
    public $ba_fax;
    public $ba_email;
    public $ba_website;
    public $ba_address1;
    public $ba_address2;
    public $ba_state_id;
    public $ba_city;
    public $ba_zip;
    public $ba_phone_call_status;
    public $ba_phone_call_invited_status;
    public $ba_phone_call_date;
    public $ba_agreement_status;
    public $ba_assessment_invited_status;
    public $ba_assessment_invited_date;
    public $ba_assessment_completed_status;
    public $ba_hipaa_compliant_status;

    public $ba_active;
    public $ba_status;
    public $ba_sign_off_date;

    public $ba_create_date;
    public $ba_update_date;

    public $_contact_name;
    public $_client_name;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->ba_id     = (isset($data['ba_id'])) ? $data['ba_id'] : null;
        $this->ba_contact_u_id     = (isset($data['ba_contact_u_id'])) ? $data['ba_contact_u_id'] : null;
        $this->ba_consultant_u_id     = (isset($data['ba_consultant_u_id'])) ? $data['ba_consultant_u_id'] : null;
        $this->ba_create_u_id     = (isset($data['ba_create_u_id'])) ? $data['ba_create_u_id'] : null;
        $this->ba_update_u_id     = (isset($data['ba_update_u_id'])) ? $data['ba_update_u_id'] : null;
        $this->ba_c_id     = (isset($data['ba_c_id'])) ? $data['ba_c_id'] : null;
        $this->ba_name     = (isset($data['ba_name'])) ? $data['ba_name'] : null;
        $this->ba_office_phone     = (isset($data['ba_office_phone'])) ? $data['ba_office_phone'] : null;
        $this->ba_office_phone_inner     = (isset($data['ba_office_phone_inner'])) ? $data['ba_office_phone_inner'] : null;
        $this->ba_direct_phone     = (isset($data['ba_direct_phone'])) ? $data['ba_direct_phone'] : null;
        $this->ba_direct_phone_inner     = (isset($data['ba_direct_phone_inner'])) ? $data['ba_direct_phone_inner'] : null;
        $this->ba_other_phone     = (isset($data['ba_other_phone'])) ? $data['ba_other_phone'] : null;
        $this->ba_other_phone_inner     = (isset($data['ba_other_phone_inner'])) ? $data['ba_other_phone_inner'] : null;
        $this->ba_fax     = (isset($data['ba_fax'])) ? $data['ba_fax'] : null;
        $this->ba_email     = (isset($data['ba_email'])) ? $data['ba_email'] : null;
        $this->ba_website     = (isset($data['ba_website'])) ? $data['ba_website'] : null;
        $this->ba_address1     = (isset($data['ba_address1'])) ? $data['ba_address1'] : null;
        $this->ba_address2     = (isset($data['ba_address2'])) ? $data['ba_address2'] : null;
        $this->ba_state_id     = (isset($data['ba_state_id'])) ? $data['ba_state_id'] : null;
        $this->ba_city     = (isset($data['ba_city'])) ? $data['ba_city'] : null;
        $this->ba_zip     = (isset($data['ba_zip'])) ? $data['ba_zip'] : null;
        $this->ba_phone_call_status     = (isset($data['ba_phone_call_status'])) ? $data['ba_phone_call_status'] : null;
        $this->ba_phone_call_invited_status     = (isset($data['ba_phone_call_invited_status'])) ? $data['ba_phone_call_invited_status'] : null;
        $this->ba_phone_call_date     = (isset($data['ba_phone_call_date'])) ? $data['ba_phone_call_date'] : null;
        $this->ba_agreement_status     = (isset($data['ba_agreement_status'])) ? $data['ba_agreement_status'] : null;
        $this->ba_assessment_invited_status     = (isset($data['ba_assessment_invited_status'])) ? $data['ba_assessment_invited_status'] : null;
        $this->ba_assessment_invited_date     = (isset($data['ba_assessment_invited_date'])) ? $data['ba_assessment_invited_date'] : null;
        $this->ba_assessment_completed_status     = (isset($data['ba_assessment_completed_status'])) ? $data['ba_assessment_completed_status'] : null;
        $this->ba_hipaa_compliant_status     = (isset($data['ba_hipaa_compliant_status'])) ? $data['ba_hipaa_compliant_status'] : null;
        $this->ba_active     = (isset($data['ba_active'])) ? $data['ba_active'] : null;
        $this->ba_status     = (isset($data['ba_status'])) ? $data['ba_status'] : null;
        $this->ba_sign_off_date     = (isset($data['ba_sign_off_date'])) ? $data['ba_sign_off_date'] : null;
        $this->ba_create_date     = (isset($data['ba_create_date'])) ? $data['ba_create_date'] : null;
        $this->ba_update_date     = (isset($data['ba_update_date'])) ? $data['ba_update_date'] : null;
        $this->_contact_name     = (isset($data['_contact_name'])) ? $data['_contact_name'] : null;
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
                'name'     => 'ba_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $authService = new \Zend\Authentication\AuthenticationService();
            $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
            $identity = $authService->getIdentity();

            if (in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_CLIENT))) {
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'ba_c_id',
                    'required' => false,
                )));
            }

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_name',
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
                'name'     => 'ba_phone_call_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_phone_call_invited_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_agreement_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_assessment_invited_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_assessment_completed_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_hipaa_compliant_status',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_firstname',
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
                'name'     => 'u_lastname',
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
            //if (!$isEdit) {
                $ee = new \Mylib\Validator\EmailExists($sl, $uId);

                $inputFilter->add($factory->createInput(array(
                    'name'     => 'u_email',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'EmailAddress',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 100,
                            ),
                        ),
                        $ee
                    ),
                )));
           // }

            $inputFilter->add($factory->createInput(array(
                'name'     => 'ba_state_id',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}