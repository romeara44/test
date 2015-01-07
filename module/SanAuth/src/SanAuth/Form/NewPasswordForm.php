<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Form\Element\Captcha;

class NewPasswordForm extends Form
{
    public function __construct()
    {
        parent::__construct('auth');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-signin');

        $this->add(array(
            'name' => 'u_password',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'nowe hasło',
            ),
        ));
        $this->add(array(
            'name' => 'u_password_repeat',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'potwierdź nowe hasło',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Zmień hasło i zaloguj',
                'id' => 'submitbutton',
            ),
        ));
    }
}