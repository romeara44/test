<?php
namespace Assessment\Form;

use Zend\Form\Form;

class ImportForm extends Form
{
    public function __construct($sl, $post = array())
    {

        parent::__construct();
        $this->setAttribute('method', 'post');

        $companyTable = $sl->get('Client\Model\CompanyTable');
        $companies[''] = 'Please Select';
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'rp_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $clients[''] = 'Please Select';

        if(isset($post['rp_c_id']) && !empty($post['rp_c_id'])) {
            $companyClients = $sl->get('Admin\Model\UserTable')->getUsersByCompany($post['rp_c_id']);
            foreach ($companyClients as $client) {
                $clients[$client->u_id] = $client->u_firstname . ' ' . $client->u_lastname;
            }
        }

        $this->add(array(
            'name' => 'rp_performed_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Performed By',
                'value_options' => $clients
            ),
        ));
        
        $this->add(array(
            'name' => 'rp_accepter_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Assignee',
                'value_options' => $clients
            ),
        ));

        $this->add(array(
            'name' => 'rp_approver_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Approver',
                'value_options' => $clients
            ),
        ));

        $this->add(array(
            'name' => 'rp_remediation_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Plan Date',
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Submit',
                'id' => 'submitbutton',
            ),
        ));
    }
}