<?php
namespace Securityreminder\Form;

use Zend\Form\Form;

class SecurityreminderForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'sr_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'sr_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $DistributiontypeTable = $sl->get('Securityreminder\Model\DistributiontypeTable');
        $types[''] = 'Please Select';
        foreach ($DistributiontypeTable->getDistributiontypes() as $key => $r) {
            $types[$key] = $r;
        }

        $this->add(array(
            'name' => 'sr_dt_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => $types
            ),
        ));

        $this->add(array(
            'name' => 'sr_launched_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Launched Date',
            ),
        ));

        $this->add(array(
            'name' => 'sr_developed_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Developed By',
            ),
        ));

        $this->add(array(
            'name' => 'sr_regulation',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Regulation',
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