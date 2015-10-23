<?php
namespace DataCrypt;

use \Zend\Db\Sql\Expression;

class DbCrypt
{
    public static function encryptValue($value, $expression = true)
    {
        $application_vars = new \Zend\Session\Container('application_vars');
        $secureDBKey = $application_vars->storage['secure_db_key'];
        
        return $expression ? new Expression('AES_ENCRYPT("' . $value . '", "' . $secureDBKey . '")') : 'AES_ENCRYPT("' . $value . '", "' . $secureDBKey . '")';
    }

    public static function decryptField($field, $expression = true)
    {
        $application_vars = new \Zend\Session\Container('application_vars');
        $secureDBKey = $application_vars->storage['secure_db_key'];

        return $expression ? new Expression('AES_DECRYPT(' . $field . ', "' . $secureDBKey . '")') : 'AES_DECRYPT(' . $field . ', "' . $secureDBKey . '")';
    }
}
