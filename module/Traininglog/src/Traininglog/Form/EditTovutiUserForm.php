<?php
// Filename: /module/Traininglog/src/Traininglog/Form/EditLitmosUserForm.php
namespace Traininglog\Form;

use Zend\Form\Form;

class EditTovutiUserForm extends Form
{
  //public function __construct($sl)
  public function __construct($name = null)
  {
      parent::__construct('edittovutiuser');
      $this->setAttribute('method', 'post');

      $this->add(array(
        'name' => 'tovutiuserid',
        'type' => 'Hidden',
        
      ));
   
      $this->add(array(
        'type' => 'hidden',
        'name' => 'companyname'
      ));
  
      $this->add(array(
        'type' => 'hidden',
        'name' => 'tovutiid'
      ));

      $teams = [];
        
      $this->add(array(
        'name' => 'user_group_id',
        'type' => 'Zend\Form\Element\Select',
        'options' => array(
            'label' => 'User Group',
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
          'label' => 'Training Type'
        )
      ));

      $this->add(array(
        'name' => 'registerdate',
        'attributes' => array(
            'type'  => 'text',
        ),
        'options' => array(
          'label' => 'Registered Date'
        )
      ));

      $this->add(array(
        'name' => 'lastvisitdate',
        'attributes' => array(
            'type'  => 'text',
        ),
        'options' => array(
          'label' => 'Last Visit Date'
        )
      ));

      $this->add( array(
        'name'     => 'status',
        'type'     => 'Zend\Form\Element\Select',
        'options'  => array(
             'label'            => 'Status'
            ,'label_attributes' => array( 'placement' => 'APPEND')
            ,'value_options'    => array(
                    'active' => 'Active',
                    'disabled' => 'Disabled',
            ),
        ),
        'attributes' => array(
            'id'     => 'status',
            'value'  => 'disabled',
        ),
      ));

      $this->add( array(
        'name'     => 'requirereset',
        'type'     => 'Zend\Form\Element\Select',
        'options'  => array(
             'label'            => 'Require Reset'
            ,'label_attributes' => array( 'placement' => 'APPEND')
            ,'value_options'    => array(
                    '1' => 'True',
                    '0' => 'False',
            ),
        ),
        'attributes' => array(
            'id'     => 'requirereset',
            'value'  => 0,
        ),
      ));
    }
}