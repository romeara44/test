<?php
namespace Traininglog\Form;

use Zend\Form\Form;

class TraininglogForm extends Form
{
    public function __construct($sl, $tlObj = null)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $companies = array();
        
        $companyTable = $sl->get('Client\Model\CompanyTable');
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        if(count($companies) != 1) {
            $companies = array('' => 'Please select') + $companies;
        }

        $this->add(array(
            'name' => 'tl_company_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'tl_title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));

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
        foreach ($traininglogtypeTable->getTraininglogtypesByCompany($identity['u_company_id']) as $key => $r) {
            $types[$key] = $r;
        }
        $types['-1'] = 'Other';

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

        $tlCId = is_object($tlObj) ? $tlObj->tl_company_id : null;
        $traininglogTable = $sl->get('Traininglog\Model\TraininglogTable');
        $trainers[''] = 'Please Select';
        foreach ($traininglogTable->getTrainers($tlCId) as $key => $r) {
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