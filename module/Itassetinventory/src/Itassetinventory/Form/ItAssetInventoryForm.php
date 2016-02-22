<?php
namespace Itassetinventory\Form;

use Zend\Form\Form;

class ItAssetInventoryForm extends Form
{
    public function __construct($sl, $iaiObj = null)
    {
        parent::__construct('user');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'iai_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $companies[''] = 'Select Client';
        $companiesPairs = $sl->get('Client\Model\CompanyTable')->getCompaniesPairs();
        foreach($companiesPairs as $key => $r) {
            $companies[$key] = $r;
        }

        $this->add(array(
            'name' => 'iai_c_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Client Name',
                'value_options' => $companies
            ),
        ));

        $locations[''] = 'Select Location';

        if(is_object($iaiObj)) {
            $locations += $sl->get('Client\Model\CompanyTable')->getCompanyLocations($iaiObj->iai_c_id);
        }

        $this->add(array(
            'name' => 'iai_location_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Location',
                'value_options' => $locations,
            ),
        ));

        $types[''] = 'Select Inventory Asset';
        $allTypes = $sl->get('Itassetinventory\Model\ItAssetInventoryTypeTable')->getAllActive();
        foreach ($allTypes as $r) {
            $types[$r->iait_id] = $r->iait_name;
        }

        $this->add(array(
            'name' => 'iai_type_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Inventory Assets',
                'value_options' => $types
            ),
        ));
    }
}