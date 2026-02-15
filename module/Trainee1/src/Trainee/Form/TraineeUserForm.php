<?php
// Filename: /module/Trainee/src/Trainee/Form/TraineeUserForm.php
namespace Trainee\Form;

use Zend\Form\Form;

class PostForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->add(array(
            'name' => 'trainee-user-fieldset',
            'type' => 'Trainee\Form\TraineeUserFieldset',
            'options' => array(
                'use_as_base_fieldset' => true
            )
        ));

        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Insert new Post'
            )
        ));
    }
}