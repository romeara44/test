<?php
namespace Note\Form;

use Zend\Form\Form;

class NoteForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('note');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'note_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'note_u_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'note_item_type',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'note_item_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'note_text',
            'attributes' => array(
                'type'  => 'textarea',
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