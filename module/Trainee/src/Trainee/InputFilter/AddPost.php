<?php
namespace Trainee\InputFilter;

use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\I18n\Validator\Alnum;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\EmailAddress;
use Zend\Validator\ValidatorChain;

class AddPost extends InputFilter
{
    public function __construct()
    {
        
        $firstname = new Input('firstname');
        $firstname->setRequired(true);
        $firstname->setValidatorChain($this->getFirstNameValidatorChain());
        $firstname->setFilterChain($this->getStringTrimFilterChain());

        $lastname = new Input('lastname');
        $lastname->setRequired(true);
        $lastname->setValidatorChain($this->getLastNameValidatorChain());
        $lastname->setFilterChain($this->getStringTrimFilterChain());

        $email = new Input('email');
        $email->setRequired(true);
        $email->setValidatorChain($this->getEmailValidatorChain());
        $email->setFilterChain($this->getStringTrimFilterChain());

        $jobtitle = new Input('jobtitle');
        $jobtitle->setRequired(true);
        $jobtitle->setValidatorChain($this->getJobTitleValidatorChain());
        $jobtitle->setFilterChain($this->getStringTrimFilterChain());

        $username = new Input('username');
        $username->setRequired(true);
        $username->setValidatorChain($this->getUserNameValidatorChain());
        $username->setFilterChain($this->getStringTrimFilterChain());

        $this->add($firstname);
        $this->add($lastname);
        $this->add($email);
        $this->add($jobtitle);
        $this->add($username);

    }

    /**
     * @return ValidatorChain
     */
    protected function getFirstNameValidatorChain()
    {
        $stringLength = new StringLength();
        $stringLength->setMin(5);
        $stringLength->setMax(50);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new Alnum(true));
        $validatorChain->attach($stringLength);

        return $validatorChain;

    }

    protected function getLastNameValidatorChain()
    {
        $stringLength = new StringLength();
        $stringLength->setMin(5);
        $stringLength->setMax(50);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new Alnum(true));
        $validatorChain->attach($stringLength);

        return $validatorChain;

    }

    protected function getEmailValidatorChain()
    {
        $stringLength = new StringLength();
        $stringLength->setMin(5);
        $stringLength->setMax(50);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new Alnum(true));
        $validatorChain->attach($stringLength);

        return $validatorChain;
    }

    protected function getJobTitleValidatorChain()
    {
        $stringLength = new StringLength();
        $stringLength->setMin(5);
        $stringLength->setMax(50);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new Alnum(true));
        $validatorChain->attach($stringLength);

        return $validatorChain;
    }

    protected function getUserNameValidatorChain()
    {
        $stringLength = new StringLength();
        $stringLength->setMin(5);
        $stringLength->setMax(50);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new Alnum(true));
        $validatorChain->attach($stringLength);

        return $validatorChain;
    }

    
    /**
     * @return FilterChain
     */
    protected function getStringTrimFilterChain()
    {
        $filterChain = new FilterChain();
        $filterChain->attach(new StringTrim());

        return $filterChain;

    }

    /**
     * @return FilterChain
     */
    protected function getStripTagsilterChain()
    {
        $filterChain = new FilterChain();
        $filterChain->attach(new StripTags());

        return $filterChain;

    }

    /**
     * @return FilterChain
     */
    protected function getStringTrimFilterChain1()
    {
        $filterChain = new FilterChain();
        $filterChain->attach(new StripTags());

        return $filterChain;

    }


    protected function t()
    {
        $filterChain = new FilterChain();
        $filterChain->attach(new EmailAddress());
    }
}