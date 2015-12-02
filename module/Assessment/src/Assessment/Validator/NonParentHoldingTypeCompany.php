<?php

namespace Assessment\Validator;

use Zend\Validator\AbstractValidator;

class NonParentHoldingTypeCompany extends AbstractValidator {
	protected $model;
	protected $sl;

	public function __construct($options = null)
    {
        parent::__construct($options);
        if ($options && is_array($options) && array_key_exists('model', $options)) {
            $this->model = $options['model'];
        }           
        if ($options && is_array($options) && array_key_exists('sl', $options)) {
            $this->sl = $options['sl'];
        }
    }

	protected $messageTemplates = array(
        0 => "Cannot create assessment with Parent company Holding type.",
    );

	public function isValid($value) {
		$this->setValue($value);
		$company = $this->sl->get('Client\Model\CompanyTable')->getCompany($value);
		if ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_PARENT && $company->c_type == \Client\Model\Company::PARENT_TYPE_HOLDING) {
			$this->error(0);
        	return false;
		}
        return true;
	}
}