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
    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::EXISTS            => "E-mail exists",
    );


    public function __construct($sl, $uId = 0)
    {
        $this->sl = $sl;
        $this->uId = (int) $uId;
        parent::__construct();
    }

    public function isValid($value)
    {
        $usersTable = $this->sl->get('Admin\Model\UserTable');

        $exist = $usersTable->checkIfUserExists($value, $this->uId);
        if ($exist) {
            $this->error(self::EXISTS);
            return false;
        }

        return true;
    }
}
