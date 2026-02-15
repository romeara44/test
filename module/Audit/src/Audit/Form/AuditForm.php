<?php
namespace Audit\Form;

use Zend\Form\Form;

class AuditForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'ar_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'audit_record_item_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $companyTable = $sl->get('Client\Model\CompanyTable');
        //$companies[''] = 'Select Client';
        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'company_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Client Name',
                'empty_option' => 'Please select...',
                'value_options' => $companies
            ),
        ));

    
        $this->add(array(
            'name' => 'a_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => ' Audit Type',
                'value_options' => array('' => 'Select Audit Type', 0 => '---', \Audit\Model\AuditRecord::$typesNames[\Audit\Model\AuditRecord::TYPE_HIPAA], \Audit\Model\AuditRecord::$typesNames[\Audit\Model\AuditRecord::TYPE_SOC])
            ),
        ));

        // $this->add(array(
        //     'name' => 'question_type',
        //     'type' => 'Zend\Form\Element\Select',
        //     'options' => array(
        //         'label' => ' Protocol','value_options' => array(\Audit\Model\AuditRecord::QUEST_TYPE_SECURITY => \Audit\Model\AuditRecord::$questTypeNames[\Audit\Model\AuditRecord::QUEST_TYPE_SECURITY], \Audit\Model\AuditRecord::QUEST_TYPE_PRIVACY => \Audit\Model\AuditRecord::$questTypeNames[\Audit\Model\AuditRecord::QUEST_TYPE_PRIVACY], \Audit\Model\AuditRecord::QUEST_TYPE_BREACH => \Audit\Model\AuditRecord::$questTypeNames[\Audit\Model\AuditRecord::QUEST_TYPE_BREACH])
        //     ),
        // ));

        $remediationPlanTable = $sl->get('Assessment\Model\RemediationPlanTable');
        
        foreach ($remediationPlanTable->getRemediationPlansWithinOneYear() as $key => $r) {
            $remediation_plans[$r->rp_id] = $r->rp_id . ' - ' . $r->rp_remediation_date;
        }

        $this->add(array(
            'name' => 'remediation_plan_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Remediation Plan',
                'empty_option' => 'Please select...',
                'value_options' => $remediation_plans
                //'value_options' => $remediation_plan_ids
            ),
        ));

        $this->add(array(
            'name' => '_policy_number',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'audit_record_status_id',
            'options' => array(
                'label' => 'Whats the Status',
                'value_options' => array(
                    '1' => 'Pass',
                    '2' => 'Fail',
                    '3' => 'Substitue',
                ),
            )
        ));

         $this->add(array(
             'name' => 'comment_text',
             'attributes' => array(
                 'type' => 'text',
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