<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Form\Element\Captcha;

class AuthForm extends Form
{
    public function __construct()
    {
        parent::__construct('auth');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', '');

        $this->add(array(
            'name' => 'u_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Email',
            )
        ));
        $this->add(array(
            'name' => 'u_password',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Password',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'remember_me',
            'options' => array(
                'label' => 'Remember me',
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Log in',
                'id' => 'submitbutton',
            ),
        ));
    }
}