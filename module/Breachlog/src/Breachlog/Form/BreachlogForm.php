<?php
namespace Breachlog\Form;

use Zend\Form\Form;

class BreachlogForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'bl_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $companyTable = $sl->get('Client\Model\CompanyTable');
        $companies[''] = 'Please Select';
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'bl_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'bl_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Breach Name',
            )
        ));

        $this->add(array(
            'name' => 'bl_date_of_occurrence',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date of Occurrence',
            )
        ));

        $this->add(array(
            'name' => 'bl_size',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Size of Breach',
            )
        ));

        $this->add(array(
            'name' => 'bl_description',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Description of the Breach Event',
            )
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