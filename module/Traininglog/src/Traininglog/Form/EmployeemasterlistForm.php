<?php
namespace Traininglog\Form;

use Zend\Form\Form;

class EmployeemasterlistForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $companies = array();
        
        $companyTable = $sl->get('Client\Model\CompanyTable');
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        if(count($companies) != 1) {
            $companies = array('0' => 'Please select') + $companies;
        }

        $this->add(array(
            'name' => 'eml_company_id[]',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'eml_list[]',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Employee Master List',
            ),
        ));
    }
}