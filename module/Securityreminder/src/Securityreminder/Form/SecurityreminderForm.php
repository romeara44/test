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

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->add(array(
            'name' => 'sr_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'sr_title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
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
                'label' => 'Distribution Type',
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

        $UserTable = $sl->get('Admin\Model\UserTable');
        foreach ($UserTable->getUsersByCompany($identity['u_company_id']) as $key => $r) {
            $users[$r->u_id] = $r->u_firstname . ' ' . $r->u_lastname;
        }

        if(count($users) != 1) {
            $users = array('' => 'Please select') + $users;
        }

        $this->add(array(
            'name' => 'sr_developed_by_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Developed By',
                'value_options' => $users
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