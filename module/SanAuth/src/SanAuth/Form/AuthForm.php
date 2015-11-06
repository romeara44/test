<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Form\Element\Captcha,
    Zend\Captcha\Image as CaptchaImage;;

class AuthForm extends Form
{
    public function __construct($urlcaptcha)
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
                'type'  => 'password',
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

        $dirdata = $_SERVER['DOCUMENT_ROOT'] . '/data';

        $captchaImage = new CaptchaImage(  array(
                'font' => $dirdata . '/fonts/arial.ttf',
                'width' => 250,
                'height' => 70,
                'dotNoiseLevel' => 100,
                'lineNoiseLevel' => 5)
        );

        $captchaImage->setImgDir($dirdata . '/captcha');
        $captchaImage->setImgUrl($urlcaptcha);

        //add captcha element...
        $this->add(array(
            'type' => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => array(
                'label' => 'Please verify you are human:',
                'label_attributes' => array(
                    'class'  => 'captcha-label'
                ),
                'captcha' => $captchaImage,
            ),
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