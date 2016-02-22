<?php
namespace Physicalsecuritychange\Form;

use Zend\Form\Form;

class PhysicalsecuritychangeForm extends Form
{
    public function __construct($sl, $pscObj = null)
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
            'name' => 'psc_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Client',
                'value_options' => $companies
            ),
        ));

        $locations = array();
        
        if ($pscObj->psc_c_id) {
            $comp_addresses = $sl->get('Client\Model\AddressTable')->getAddresses($pscObj->psc_c_id, \Client\Model\AddressItem::COMPANY_TYPE);
            foreach ($comp_addresses as $addr) {
                $locations[$addr->cadr_id] = 'Location' . $addr->cadr_id;
            }
        }        

        if(count($locations) != 1) {
            $locations = array('' => 'Please select') + $locations;
        }

        $this->add(array(
            'name' => 'psc_adr_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Location',
                'value_options' => $locations
            ),
        ));

        $types = array(
            '1' => 'Maintenance',
            '2' => 'Replacement',
            '3' => 'Repair',
        );
        
        $this->add(array(
            'name' => 'psc_change_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => $types
            ),
        ));

        $this->add(array(
            'name' => 'psc_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'psc_active',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
    }
}