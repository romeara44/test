<?php
// Filename: /module/Traininglog/src/Traininglog/Form/EditLitmosUserForm.php
namespace Traininglog\Form;

use Zend\Form\Form;

class EditLitmosUserForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('editlitmosuser');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
         ));
   
         $this->add(array(
            'type' => 'hidden',
            'name' => 'companyname'
         ));
  
        $this->add(array(
            'type' => 'hidden',
            'name' => 'litmosuserid'
        ));
        
        $teams = [];
        
        $this->add(array(
            'name' => 'team_choice_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Team',
                'empty_option' => 'Please select...',
                'value_options' => $teams
            ),
        ));

          
         $this->add(array(
            'name' => 'username',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'User Name'
            )
         ));
   
         $this->add(array(
            'name' => 'firstname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
               'label' => 'Employees First Name',
               'label_attributes' => array(
                    'style' => 'width: 300px',
               )
            )
         ));
   
         $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'type'  => 'text',
            ),
           'options' => array(
              'label' => 'Employees Last Name',
              'label_attributes' => array(
                'style' => 'width: 300px',
                )
           )
        ));
   
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
            ),
           'options' => array(
              'label' => 'Email'
           )
        ));

        $this->add(array(
            'name' => 'department',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'Department'
            )
         ));

         $this->add(array(
            'name' => 'jobtitle',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'Job Title'
            )
         ));

         $this->add(array(
            'name' => 'trainingtype',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'TrainingType'
            )
         ));

         $this->add(array(
            'name' => 'createddate',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'Created Date'
            )
         ));

         $this->add(array(
            'name' => 'inactivedate',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
              'label' => 'Inactive Date'
            )
         ));
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