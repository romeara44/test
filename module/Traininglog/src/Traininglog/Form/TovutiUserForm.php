<?php
// Filename: /module/Traininglog/src/Traininglog/Form/TovutiUserForm.php
namespace Traininglog\Form;

use Zend\Form\Form;

class TovutiUserForm extends Form
{
  public function __construct($sl, $dep)
  //public function __construct($name = null)
  {
      parent::__construct('edittovutiuser');
      $this->setAttribute('method', 'post');

      $authService = new \Zend\Authentication\AuthenticationService();
      $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
      //$identity = $authService->getIdentity();
      //$companyObject = $sl->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);

      $this->add(array(
        'name' => 'tovutiuserid',
        'type' => 'Hidden',
        
      ));
   
      // $this->add(array(
      //   'type' => 'hidden',
      //   'name' => 'companyname'
      // ));
  
      // $this->add(array(
      //   'type' => 'hidden',
      //   'name' => 'tovutiid'
      // ));

      $this->add(array(
        'name' => 'companyname',
        'attributes' => array(
            'type'  => 'text',
        ),
        'options' => array(
          'label' => 'Company Name'
        )
      ));


      $this->add(array(
        'name' => 'tovutiid',
        'attributes' => array(
            'type'  => 'text',
        ),
        'options' => array(
          'label' => 'Tovuti ID'
        )
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

      //$departments1 = $sl->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($companyObject->c_name);
      
      $this->add(array(
        'name' => 'department',
        'type' => 'Zend\Form\Element\Select',
        'options' => array(
            'label' => 'Department',
            'empty_option' => 'Please select...',
            'value_options' => $dep

        ),
      ));
  //     $group_id->setLabel('Category:')
  //              ->addMultiOptions(array(
  //               '1' => 'A',
  //               '2' => 'B', 
  //               '3' => 'C',
  //               '4' => 'D'                       
  //                   ))               
  //          ->setRequired(true)
  //          ->setDecorators(array('ViewHelper','Errors'));
  //  $group_id->setValue(3);

  //companyObject = $sl->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
      //$departmentTable = $sl->get('Traininglog\Model\TovutiUserTable');
      // $jobTitle = $sl->get('Traininglog\Model\TovutiUserTable')->getAllJobTitlesByCompanyName($companyObject->c_name);
      // $this->add(array(
      //   'name' => 'jobtitle',
      //   'type' => 'Zend\Form\Element\Select',
      //   'options' => array(
      //       'label' => 'Job Title',
      //       'empty_option' => 'Please select...',
      //       'value_options' => $jobTitle
      //   ),
      // ));

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
        'required' => true,
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
    //   $inputFilter->add(array(
    //     'name'     => 'id',
    //     'required' => true,
    //     'filters'  => array(
    //         array('name' => 'Int'),
    //     ),
    // ));

      $this->add(array(
        'name' => 'lastname',
        'required' => true,
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
   
      // $this->add(array(
      //   'name' => 'email',
      //   'required' => true,
      //   'attributes' => array(
      //       'type'  => 'text',
      //   ),
      //   // 'options' => array(
      //   //   'label' => 'Email',
      //   //   'label_attributes' => array(
      //   //     'style' => 'width: 300px',
      //   //   )
      //   //)
      // //   label {
      // //     display: block;
      // // }
      // ));
      $this->add(array(
        'name' => 'email',
        'required' => true,
        'attributes' => array(
            'type'  => 'text',
            'data-toggle'    => 'tooltip',
            'data-placement' => 'left',
            'title'          => 'Employees email address at which they will receive all training notification emails.',
        ),
        // 'options' => array(
        //   'label' => 'Email',
        //   'label_attributes' => array(
        //     'style' => 'width: 300px',
        //   )
        //)
      //   label {
      //     display: block;
      // }
      ));

      // $this->add(array(
      //   'name'       => 'submit1',
      //   'type'       => 'Submit1',
      //   'attributes' => array(
      //       'value'          => 'Save',
      //       'id'             => 'submitbutton',
      //       'data-toggle'    => 'tooltip',
      //       'data-placement' => 'left',
      //       'title'          => 'Press me I am a button :D',
      //       ),
      //   ));

      // $this->add(array(
      //   'name' => 'department',
      //   'attributes' => array(
      //       'type'  => 'text',
      //   ),
      //   'options' => array(
      //     'label' => 'Department'
      //   )
      // ));

      

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

      $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Go',
            'id' => 'submitbutton',
        ),
    ));

    $this->add(array(
      'name' => 'cancel',
      'type' => 'Submit',
      'attributes' => array(
          'value' => 'Cancel',
          'id' => 'cancelbutton',
      ),
  ));
    }
}