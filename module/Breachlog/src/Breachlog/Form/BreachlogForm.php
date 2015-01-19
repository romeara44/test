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
            'name' => 'bl_invest_led_by',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Investigation Led By',
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
            'name' => 'bl_date_invest_start',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Investigation Started',
            )
        ));
        
        $this->add(array(
            'name' => 'bl_date_invest_complete',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date Investigation Complete',
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
            'name' => 'bl_initials_approver',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Initials',
            )
        ));
        
        $this->add(array(
            'name' => 'bl_initials',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Initials',
            )
        ));

        $this->add(array(
            'name' => 'bl_approver_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Reviewed and approved by',
            )
        ));
        
        $this->add(array(
            'name' => 'bl_accepter_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Agreed to and accepted by',
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