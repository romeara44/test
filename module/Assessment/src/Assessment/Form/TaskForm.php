<?php
namespace Assessment\Form;

use Zend\Form\Form;

class TaskForm extends Form
{
    public function __construct($sl, $rpObj = null)
    {

        parent::__construct('rpa');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'rpa_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'rpa_rp_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'rpa_threat',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Threat',
            )
        ));

        $this->add(array(
            'name' => 'rpa_action_plan',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Action plan',
            )
        ));

        $this->add(array(
            'name' => 'rpa_policy',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Policy Number',
            )
        ));

        $this->add(array(
            'name' => 'rpa_risk_level',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Risk score',
                'value_options' => \Assessment\Model\Remediationplanaction::$levelsNames
            ),
        ));

        $this->add(array(
            'name' => 'rpa_status',
            'type'  => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    0 => \Assessment\Model\Remediationplanaction::$statusesNames[\Assessment\Model\Remediationplanaction::STATUS_TODO],
                    10 => \Assessment\Model\Remediationplanaction::$statusesNames[\Assessment\Model\Remediationplanaction::STATUS_PENDING_APPROVAL],
                    30 => \Assessment\Model\Remediationplanaction::$statusesNames[\Assessment\Model\Remediationplanaction::STATUS_PENDING_IMPLEMENTATION],
                    20 => \Assessment\Model\Remediationplanaction::$statusesNames[\Assessment\Model\Remediationplanaction::STATUS_COMPLETED],
                    40 => \Assessment\Model\Remediationplanaction::$statusesNames[\Assessment\Model\Remediationplanaction::STATUS_AUDITED]
                ),
            )
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $contacts[''] = 'Please Select';
        $contacts[0] = '---';
        foreach ($userTable->getContactsByCompanyId($rpObj->rp_c_id, true) as $key => $r) {
            $contacts[$key] = $r;
        }

        $this->add(array(
            'name' => 'rpa_contact_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Assignee',
                'value_options' => $contacts
            ),
        ));

        /*$userTable = $sl->get('Admin\Model\UserTable');
        $contacts = array();
        $contacts[''] = 'Please Select';
        $contacts[0] = '---';
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $contacts[$key] = $r;
        }*/

        $this->add(array(
            'name' => 'rpa_approver_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Approver',
                'value_options' => $contacts
            ),
        ));

        $this->add(array(
            'name' => 'rpa_target_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Target Date',
            )
        ));

        $this->add(array(
            'name' => 'rpa_add_attachments',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => 'Add Attachments',
                'use_hidden_element' => false,
                'checked_value' => '1',
                'unchecked_value' => '0'
            ),
            'attributes' => array(
                'checked' => 'checked',
                'id' => 'rpa_add_attachments',
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