<?php

namespace Client\Validator;

use Zend\Validator\AbstractValidator;

class ChildCompanies extends AbstractValidator {
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
        0 => "Company cannot be daughter.",
    );

	public function isValid($values) {
        if ($this->model->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD) {
            return true;
        }
		$this->setValue($values);
		$parent_companies_ids = $this->sl->get('Client\Model\CompanyTable')->getParentCompaniesIds();
		if (in_array($this->model->c_id, $values) || array_intersect($values, $parent_companies_ids)) {
			$this->error(0);
        	return false;
		}
        return true;
	}
}