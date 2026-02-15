<?php
// Filename: /module/Trainee/src/Trainee/Form/TraineeUserFieldset.php
namespace Trainee\Form;

//use Blog\Model\Post;
use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator\ClassMethods;

class PostFieldset extends Fieldset
{
   public function __construct($name = null, $options = array())
   {
        parent::__construct($name, $options);

        $this->setHydrator(new ClassMethods(false));
        $this->setObject(new Post());

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
               'label' => 'First Name'
            )
         ));
   
         $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'type'  => 'text',
            ),
           'options' => array(
              'label' => 'Last Name'
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
   }
}