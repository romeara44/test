<?php
// Filename: /module/Trainee/src/Trainee/Form/PostFieldset.php
namespace Trainee\Form;

use Trainee\Model\Post;
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
            'type' => 'text',
            'name' => 'text',
            'options' => array(
               'label' => 'The Text'
            )
      ));

      $this->add(array(
            'type' => 'text',
            'name' => 'title',
            'options' => array(
               'label' => 'Trainee Title'
            )
      ));
   }
}