<?php
namespace Breachlog\Form;

use Zend\Form\Form;

class BreachremediationplanForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $regulationTable = $sl->get('Traininglog\Model\RegulationTable');

        $regulations = $regulationTable->getRegulationsWithCategories();
        array_unshift($regulations, 'Please Select');
        $regulations['-1'] = 'Other';
        
        $this->add(array(
            'name' => '_brp_cur_regulations',
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
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
}