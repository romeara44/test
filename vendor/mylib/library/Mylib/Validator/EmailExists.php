<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Mylib\Validator;

class EmailExists extends \Zend\Validator\AbstractValidator
{
    const EXISTS            = 'emailExists';
    private $sl;
    private $uId;
    private $post;
    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::EXISTS            => "E-mail exists",
    );


    public function __construct($sl, $uId = 0, $post = null)
    {
        $this->sl = $sl;
        $this->uId = (int) $uId;
        $this->post = $post;
        parent::__construct();
    }

    public function isValid($value)
    {
        $usersTable = $this->sl->get('Admin\Model\UserTable');
        $u_company_id = empty($this->post['u_company_id']) ? 0 : (int)$this->post['u_company_id'];
        $exist = $usersTable->checkIfUserExists($value, $this->uId, $u_company_id);
        if ($exist) {
            $this->error(self::EXISTS);
            return false;
        }

        return true;
    }
}
