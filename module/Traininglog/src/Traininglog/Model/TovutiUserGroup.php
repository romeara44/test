<?php
// module/Traininglog/src/traininglog/Model/TovutiUserGroup.php
namespace Traininglog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

  
class TovutiUserGroup
{
    const USER_GROUP_PUBLIC = 1;
    const USER_GROUP_REGISTERD = 2;
    const USER_GROUP_SUB_ADMINISTRATOR = 20;
    const USER_GROUP_SITE_ADMINISTRATOR = 32;
    const USER_GROUP_TEAM_ADMINSTRATOR = 51;

    public $tovuti_user_group_id;
    public $id;
    public $parent_id;
    public $lft;
    public $rgt;
    public $title;
    public $company_name;
    public $group_name;

    //protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->tovuti_user_group_id = (isset($data['tovuti_user_group_id'])) ? $data['tovuti_user_group_id'] : null;
        $this->id                   = (isset($data['id'])) ? $data['id'] : null;
        $this->parent_id            = (isset($data['parent_id'])) ? $data['parent_id'] : null;
        $this->lft                  = (isset($data['lft'])) ? $data['lft'] : null;
        $this->rgt                  = (isset($data['rgt'])) ? $data['rgt'] : null;
        $this->title                = (isset($data['title'])) ? $data['title'] : null;
        $this->company_name         = (isset($data['company_name'])) ? $data['company_name'] : null;
        $this->group_name           = (isset($data['group_name'])) ? $data['group_name'] : null;

    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}