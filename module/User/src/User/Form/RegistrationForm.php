<?php
namespace User\Form;

use Zend\Form\Form;
use Admin\Model\RoleTable,
    Zend\Form\Element\Captcha,
    Zend\Captcha\Image as CaptchaImage;

class RegistrationForm extends Form
{
    public function __construct($sl, $urlcaptcha)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'u_firstname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'First name <span class="span-required">*</span>',
            )
        ));

        $this->add(array(
            'name' => 'u_lastname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Last Name <span class="span-required">*</span>',
            ),
        ));

        $this->add(array(
            'name' => 'u_company',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Company <span class="span-required">*</span>',
            ),
        ));

        $this->add(array(
            'name' => 'u_office_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Office Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_office_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_direct_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Direct Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_direct_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_cell_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Cell Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail <span class="span-required">*</span>',
            ),
        ));

        $this->add(array(
            'name' => 'u_confirm_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Confirm E-mail <span class="span-required">*</span>',
            ),
        ));

        $dirdata = $_SERVER['DOCUMENT_ROOT'] . '/data';

        $captchaImage = new CaptchaImage(  array(
                'font' => $dirdata . '/fonts/arial.ttf',
                'width' => 250,
                'height' => 100,
                'dotNoiseLevel' => 40,
                'lineNoiseLevel' => 3)
        );
        $captchaImage->setImgDir($dirdata.'/captcha');
        $captchaImage->setImgUrl($urlcaptcha);
 
        //add captcha element...
        $this->add(array(
            'type' => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => array(
                'label' => 'Please verify you are human',
                'captcha' => $captchaImage,
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
}