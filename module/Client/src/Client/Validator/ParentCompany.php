<?php

namespace Client\Validator;

use Zend\Validator\AbstractValidator;

class ParentCompany extends AbstractValidator {
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
        0 => "Company cannot be parent.",
    );

	public function isValid($value) {
        if (!$value || $this->model->c_rel_type == \Client\Model\Company::RELATION_TYPE_PARENT) {
            return true;
        }
		$this->setValue($value);
		$company = $this->sl->get('Client\Model\CompanyTable')->getCompany($value);
		if ($this->model->c_id == $value || $company->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD) {
			$this->error(0);
        	return false;
		}
        return true;
	}
}