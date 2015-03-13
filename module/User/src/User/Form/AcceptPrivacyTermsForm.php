<?php
namespace User\Form;

use Zend\Form\Form;
use Admin\Model\RoleTable;

class AcceptPrivacyTermsForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'agreeterms',
            'options' => array(
                'label' => 'I agree to all terms and conditions',
                'label_attributes' => array(
                    'class'  => 'agreeterms-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 'no'
            )
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