<?php
namespace Traininglog\Form;

use Zend\Form\Form;

class TraininglogForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'tl_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'tl_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $traininglogtypeTable = $sl->get('Traininglog\Model\TraininglogtypeTable');
        $types[''] = 'Please Select';
        foreach ($traininglogtypeTable->getTraininglogtypes() as $key => $r) {
            $types[$key] = $r;
        }

        $this->add(array(
            'name' => 'tl_tlt_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => $types
            ),
        ));

        $regulationTable = $sl->get('Traininglog\Model\RegulationTable');

        $regulations = $regulationTable->getRegulationsWithCategories();
        array_unshift($regulations, 'Please Select');
        $regulations['-1'] = 'Other';
        
        $this->add(array(
            'name' => '_tl_cur_regulations',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Type',
                'value_options' => $regulations
            ),
        ));

        $this->add(array(
            'name' => 'tl_conducted_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Conducted Date',
            ),
        ));

        $this->add(array(
            'name' => 'tl_hire_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Hire Date',
            ),
        ));

        $traininglogTable = $sl->get('Traininglog\Model\TraininglogTable');
        $trainers[''] = 'Please Select';
        foreach ($traininglogTable->getTrainers() as $key => $r) {
            $trainers[$key] = $r;
        }

        $trainers['-1'] = 'Other';

        $this->add(array(
            'name' => '_tl_trainer',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Trainer',
                'value_options' => $trainers
            ),
        ));

        $this->add(array(
            'name' => 'tl_regulation',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Regulation',
            ),
        ));

        $this->add(array(
            'name' => 'tl_attendees',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Attendees',
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