<?php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TraininglogRegulation
{

    public $tlrg_id;
    public $tlrg_tl_id;
    public $tlrg_rg_id;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tlrg_id    = (isset($data['tlrg_id'])) ? $data['tlrg_id'] : null;
        $this->tlrg_tl_id = (isset($data['tlrg_tl_id'])) ? $data['tlrg_tl_id'] : null;
        $this->tlrg_rg_id = (isset($data['tlrg_rg_id'])) ? $data['tlrg_rg_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}