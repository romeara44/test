<?php
namespace Admin\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\EmailExists;
use Zend\Validator\Digits;

class User
{
    const ROLE_ADMIN = 1;
    const ROLE_SENIOR_CONSULTANT = 2;
    const ROLE_CONSULTANT = 3;
    const ROLE_SALES_REP = 4;
    const ROLE_CLIENT = 5;
    const ROLE_BUSINESS_ASSOCIATE = 6;
    const ROLE_PARTIAL = 7;
    const ROLE_TRAIL = 8;

    /*
     * u_active
     */
    const STATUS_UNACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public $u_id;
    public $u_role_id;
    public $u_senior_consultant_u_id;
    public $u_company_id;
    public $u_password;
    public $u_firstname;
    public $u_lastname;
    public $u_title;
    public $u_company;
    public $u_email;
    public $u_office_phone;
    public $u_office_phone_inner;
    public $u_direct_phone;
    public $u_direct_phone_inner;
    public $u_cell_phone;
    public $u_other_phone;
    public $u_other_phone_inner;
    public $u_fax;
    public $u_address1;
    public $u_address2;
    public $u_state_id;
    public $u_city;
    public $u_zip;
    public $u_active;
    public $u_hash;
    public $u_sent_password;
    public $u_status;
    public $u_confirm_email;
    public $u_register;
    public $u_first_login;
    public $u_confirmed;
    public $u_company_id_admin;
    public $u_grant_to_disclosures;
    public $u_grant_to_breach;
    public $u_forgot_password;
    public $u_failed_logins_count;
    public $u_locked;
    public $u_locked_unlocked_date;
    public $u_modules_access_code;
    public $u_modules_access_code_created;
    public $u_create_date;

    public $_rolename;
    public $_username;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->u_id     = (isset($data['u_id'])) ? $data['u_id'] : null;
        $this->u_role_id     = (isset($data['u_role_id'])) ? $data['u_role_id'] : null;
        $this->u_senior_consultant_u_id     = (isset($data['u_senior_consultant_u_id'])) ? $data['u_senior_consultant_u_id'] : null;
        $this->u_company_id     = (isset($data['u_company_id'])) ? $data['u_company_id'] : null;
        $this->u_password     = (isset($data['u_password'])) ? $data['u_password'] : null;
        $this->u_firstname     = (isset($data['u_firstname'])) ? $data['u_firstname'] : null;
        $this->u_lastname     = (isset($data['u_lastname'])) ? $data['u_lastname'] : null;
        $this->u_title     = (isset($data['u_title'])) ? $data['u_title'] : null;
        $this->u_company     = (isset($data['u_company'])) ? $data['u_company'] : null;
        $this->u_email     = (isset($data['u_email'])) ? $data['u_email'] : null;
        $this->u_office_phone     = (isset($data['u_office_phone'])) ? $data['u_office_phone'] : null;
        $this->u_office_phone_inner     = (isset($data['u_office_phone_inner'])) ? $data['u_office_phone_inner'] : null;
        $this->u_direct_phone     = (isset($data['u_direct_phone'])) ? $data['u_direct_phone'] : null;
        $this->u_direct_phone_inner     = (isset($data['u_direct_phone_inner'])) ? $data['u_direct_phone_inner'] : null;
        $this->u_cell_phone     = (isset($data['u_cell_phone'])) ? $data['u_cell_phone'] : null;
        $this->u_other_phone     = (isset($data['u_other_phone'])) ? $data['u_other_phone'] : null;
        $this->u_other_phone_inner     = (isset($data['u_other_phone_inner'])) ? $data['u_other_phone_inner'] : null;
        $this->u_fax     = (isset($data['u_fax'])) ? $data['u_fax'] : null;
        $this->u_address1     = (isset($data['u_address1'])) ? $data['u_address1'] : null;
        $this->u_address2     = (isset($data['u_address2'])) ? $data['u_address2'] : null;
        $this->u_state_id     = (isset($data['u_state_id'])) ? $data['u_state_id'] : null;
        $this->u_city     = (isset($data['u_city'])) ? $data['u_city'] : null;
        $this->u_zip     = (isset($data['u_zip'])) ? $data['u_zip'] : null;
        $this->u_active     = (isset($data['u_active'])) ? $data['u_active'] : null;
        $this->u_hash     = (isset($data['u_hash'])) ? $data['u_hash'] : null;
        $this->u_sent_password     = (isset($data['u_sent_password'])) ? $data['u_sent_password'] : null;
        $this->u_status     = (isset($data['u_status'])) ? $data['u_status'] : null;
        $this->_rolename     = (isset($data['_rolename'])) ? $data['_rolename'] : null;
        $this->u_confirm_email     = (isset($data['u_confirm_email'])) ? $data['u_confirm_email'] : null;
        $this->u_register     = (isset($data['u_register'])) ? $data['u_register'] : null;
        $this->u_first_login     = (isset($data['u_first_login'])) ? $data['u_first_login'] : null;
        $this->u_confirmed     = (isset($data['u_confirmed'])) ? $data['u_confirmed'] : null;
        $this->u_company_id_admin     = (isset($data['u_company_id_admin'])) ? $data['u_company_id_admin'] : null;
        $this->u_grant_to_disclosures = (isset($data['u_grant_to_disclosures'])) ? $data['u_grant_to_disclosures'] : null;
        $this->u_grant_to_breach = (isset($data['u_grant_to_breach'])) ? $data['u_grant_to_breach'] : null;
        $this->u_forgot_password     = (isset($data['u_forgot_password'])) ? $data['u_forgot_password'] : null;
        $this->u_failed_logins_count     = (isset($data['u_failed_logins_count'])) ? $data['u_failed_logins_count'] : null;
        $this->u_locked     = (isset($data['u_locked'])) ? $data['u_locked'] : null;
        $this->u_locked_unlocked_date     = (isset($data['u_locked_unlocked_date'])) ? $data['u_locked_unlocked_date'] : null;
        $this->u_modules_access_code     = (isset($data['u_modules_access_code'])) ? $data['u_modules_access_code'] : null;
        $this->u_modules_access_code_created     = (isset($data['u_modules_access_code_created'])) ? $data['u_modules_access_code_created'] : null;
        $this->u_create_date     = (isset($data['u_create_date'])) ? $data['u_create_date'] : null;
        $this->_username     = (isset($data['_username'])) ? $data['_username'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getLoginInputFilter($sl)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_email',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_password',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'remember_me',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getForgetInputFilter($sl)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $ee = new \Mylib\Validator\EmailExists($sl);

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
                            'allow' => \Zend\Validator\Hostname::ALLOW_ALL
                        ),
                    ),
                    //$ee
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getSimpleInputFilter($sl, $isEdit = false, $uId = 0, $isProfile = false, $uPassword = '')
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_role_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_senior_consultant_u_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_state_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_confirm_password',
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

            if (!$isProfile) {
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
                                'allow' => \Zend\Validator\Hostname::ALLOW_ALL
                            ),
                        ),
                        $ee
                    ),
                )));
            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getClientInputFilter($sl, $isEdit = false, $uId = 0, $post = null)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_company_id',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_role_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_senior_consultant_u_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_state_id',
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
                $ee = new \Mylib\Validator\EmailExists($sl, $uId, $post);

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
                                'allow' => \Zend\Validator\Hostname::ALLOW_ALL
                            ),
                        ),
                        $ee
                    ),
                )));
            //}

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getRegistrationInputFilter($sl)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();


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

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_company',
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

            $ee = new \Mylib\Validator\EmailExists($sl);

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
                            'allow' => \Zend\Validator\Hostname::ALLOW_ALL
                        ),
                    )
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'u_confirm_email',
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
                            'allow' => \Zend\Validator\Hostname::ALLOW_ALL
                        ),
                    ),
                    array(
                        'name' => 'Identical',
                        'options' => array(
                            'token' => 'u_email',
                            'messages' => array(\Zend\Validator\Identical::NOT_SAME => 'The email is mis matched')
                        )
                    ),
                    $ee
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getAcceptPrivacyTermsInputFilter($sl)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();


            $inputFilter->add($factory->createInput(array(
                'name'       => 'agreeterms',
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                Digits::NOT_DIGITS => 'You must agree to the privacy and terms of use.',
                            ),
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}