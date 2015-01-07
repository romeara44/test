<?php
namespace Businessassociate\Form;

use Zend\Form\Form;

class BusinessassociateForm extends Form
{
    public function __construct($sl)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'ba_id',
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
            'name' => 'ba_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Company',
                'value_options' => $companies
            ),
        ));

        $this->add(array(
            'name' => 'ba_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            )
        ));

        $this->add(array(
            'name' => 'ba_office_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Office Phone',
            ),
        ));

        $this->add(array(
            'name' => 'ba_office_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));


        $this->add(array(
            'name' => 'ba_direct_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Direct Phone',
            ),
        ));

        $this->add(array(
            'name' => 'ba_direct_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'ba_other_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'ba_other_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'ba_fax',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Fax',
            ),
        ));

        $this->add(array(
            'name' => 'ba_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail',
            ),
        ));

        $this->add(array(
            'name' => 'ba_website',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Website',
            ),
        ));

        $this->add(array(
            'name' => 'ba_address1',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 1',
            ),
        ));

        $this->add(array(
            'name' => 'ba_address2',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Address 2',
            ),
        ));

        $this->add(array(
            'name' => 'ba_city',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'City',
            ),
        ));

        $stateTable = $sl->get('Admin\Model\StateTable');
        $states[''] = 'Please Select';
        $states[0] = '---';
        foreach ($stateTable->getStates() as $key => $r) {
            $states[$key] = $r;
        }

        $this->add(array(
            'name' => 'ba_state_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'State',
                'value_options' => $states
            ),
        ));

        $this->add(array(
            'name' => 'ba_zip',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'ZIP Code',
            ),
        ));

        $this->add(array(
            'name' => 'ba_phone_call_status',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Completed',
            ),
        ));

        $this->add(array(
            'name' => 'ba_phone_call_invited_status',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Invited',
            ),
        ));


        $this->add(array(
            'name' => 'ba_agreement_status',
            'type'  => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    1 => 'Required',
                    0 => 'Not Required',
                ),
            )
        ));

        $this->add(array(
            'name' => 'ba_assessment_invited_status',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Invited',
            ),
        ));

        $this->add(array(
            'name' => 'ba_assessment_completed_status',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Completed',
            ),
        ));

        $this->add(array(
            'name' => 'ba_hipaa_compliant_status',
            'type'  => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    1 => 'Yes',
                    0 => 'No',
                ),
            )
        ));

        // contact person

        $this->add(array(
            'name' => 'u_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'u_firstname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'First name',
            )
        ));
        $this->add(array(
            'name' => 'u_lastname',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Last Name',
            ),
        ));

        $this->add(array(
            'name' => 'u_title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));
        $this->add(array(
            'name' => 'u_office_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Office Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_office_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_direct_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Direct Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_direct_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_cell_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Cell Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Other Phone',
            ),
        ));

        $this->add(array(
            'name' => 'u_other_phone_inner',
            'attributes' => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name' => 'u_fax',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Fax',
            ),
        ));

        $this->add(array(
            'name' => 'u_email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail',
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