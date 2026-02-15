<?php

namespace Trainee\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Stdlib\Hydrator;
use Zend\Stdlib\Hydrator\ClassMethods;


class Add extends Form
{
    
    public function __construct($sl)
    {
        
        parent::__construct($sl);
        $this->setHydrator(new ClassMethods());
        // $this->setAttribute('method', 'post');

        // $this->add(array(
        //     'type' => 'hidden',
        //     'name' => 'id'
        //  ));
   
        //  $this->add(array(
        //     'type' => 'hidden',
        //     'name' => 'companyname'
        //  ));
  
        // $this->add(array(
        //     'type' => 'hidden',
        //     'name' => 'litmosuserid'
        // ));
        
        // $teams = [];
        
        // $this->add(array(
        //     'name' => 'team_choice_id',
        //     'type' => 'Zend\Form\Element\Select',
        //     'options' => array(
        //         'label' => 'Team',
        //         'empty_option' => 'Please select...',
        //         'value_options' => $teams
        //     ),
        // ));

        // $this->add(array(
        //     'name' => 'department',
        //     'attributes' => array(
        //         'type'  => 'text',
        //     ),
        //     'options' => array(
        //       'label' => 'Department'
        //     )
        //  ));

        $department = new Element\Select('department');
        $department->setLabel('Department');
        $department->setAttribute('class', 'form-control');
        //$department-setValueOptions(array());

        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Employees First Name');
        $firstname->setAttribute('class', 'form-control');

        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Employees Last Name');
        $lastname->setAttribute('class', 'form-control');

        $email = new Element\Text('email');
        $email->setLabel('Email');
        $email->setAttribute('class', 'form-control');

        $jobtitle = new Element\Text('jobtitle');
        $jobtitle->setLabel('Job Title');
        $jobtitle->setAttribute('class', 'form-control');

        $username = new Element\Text('username');
        $username->setLabel('User Name');
        $username->setAttribute('class', 'form-control');

        $traineetype = new Element\Select('traineetype');
        $traineetype->setLabel('Trainee Type');
        $traineetype->setAttribute('class', 'form-control');
        //$department-setValueOptions(array());

        $submit = new Element\Submit('submit');
        $submit->setValue('Add Post');
        $submit->setAttribute('class', 'btn btn-primary');

        $this->add($department);
        $this->add($firstname);
        $this->add($lastname);
        $this->add($email);
        $this->add($jobtitle);
        $this->add($username);
        $this->add($traineetype);
        $this->add($submit);
        
        


        // $this->add(array(
        //     'name' => 'submit',
        //     'attributes' => array(
        //         'type'  => 'submit',
        //         'value' => 'Save',
        //         'id' => 'submitbutton',
        //     ),
        // ));
    }
}