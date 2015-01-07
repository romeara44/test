<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Form\Element\Captcha;

class ForgetForm extends Form
{
    public function __construct()
    {
        parent::__construct('auth');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-signin');

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
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Zaloguj',
                'id' => 'submitbutton',
            ),
        ));
    }
}