<?php
namespace Breachlog\Form;

use Zend\Form\Form;

class TaskForm extends Form
{
    public function __construct($sl, $brpObj = null)
    {

        parent::__construct('brpa');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'brpa_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'brpa_brp_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'brpa_task',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Task',
            )
        ));

        /*$this->add(array(
            'name' => 'brpa_action_plan',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Action plan',
            )
        ));*/

        $this->add(array(
            'name' => 'brpa_status',
            'type'  => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    0 => \Breachlog\Model\Breachremediationplanaction::$statusesNames[\Breachlog\Model\Breachremediationplanaction::STATUS_TODO],
                    10 => \Breachlog\Model\Breachremediationplanaction::$statusesNames[\Breachlog\Model\Breachremediationplanaction::STATUS_PENDING_APPROVAL],
                    20 => \Breachlog\Model\Breachremediationplanaction::$statusesNames[\Breachlog\Model\Breachremediationplanaction::STATUS_COMPLETED],
                ),
            )
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $contacts[''] = 'Please Select';
        $contacts[0] = '---';
        foreach ($userTable->getContactsByCompanyId($brpObj->brp_c_id, true) as $key => $r) {
            $contacts[$key] = $r;
        }

        $this->add(array(
            'name' => 'brpa_contact_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Assignee',
                'value_options' => $contacts
            ),
        ));

        $userTable = $sl->get('Admin\Model\UserTable');
        $contacts = array();
        $contacts[''] = 'Please Select';
        $contacts[0] = '---';
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $contacts[$key] = $r;
        }

        $this->add(array(
            'name' => 'brpa_approver_u_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Approver',
                'value_options' => $contacts
            ),
        ));

        $this->add(array(
            'name' => 'brpa_target_date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Target Date',
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