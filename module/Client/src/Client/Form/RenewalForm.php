<?php
namespace Client\Form;

use Zend\Form\Form;

class RenewalForm extends Form
{
    public function __construct($sl, $companyObj)
    {
        parent::__construct('user');

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'c_renewal_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Renewal Date',
            ),
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $consultants[''] = 'Please select';
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $consultants[$key] = $r;
        }
        if ($companyObj->c_primary_contact_u_id) {
            $primary_contact = $userTable->getUser($companyObj->c_primary_contact_u_id);
            if ($primary_contact) {
                $consultants[$companyObj->c_primary_contact_u_id] = $primary_contact->u_firstname . ' ' . $primary_contact->u_lastname;
            }            
        }
        foreach ($userTable->getUsersByCompany($companyObj->c_id) as $key => $r) {
            $consultants[$key] = $r->u_firstname . ' ' . $r->u_lastname;
        }

        $recipients = array();
        if ($companyObj->c_renewal_email_recipients) {
            $recipients = explode(',', $companyObj->c_renewal_email_recipients);
        }

        $select = new \Zend\Form\Element\Select('c_renewal_email_recipients');
        $select->setLabel('Recipients');
        $select->setValueOptions($consultants);
        $select->setAttributes(array(
            'multiple' => 'multiple',
        ));
        $select->setValue($recipients);
        $this->add($select);

        $checkbox = new \Zend\Form\Element\Checkbox('c_deny_renewal_email_sending');
        $checkbox->setLabel('Deny renewal email sending');
        $checkbox->setValue(1);
        if ($companyObj->c_deny_renewal_email_sending) {
            $checkbox->setAttributes(array(
                'checked' => 'checked',
            ));
        }        
        $this->add($checkbox);

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