<?php
namespace DataCrypt;

// if(!in_array('DataCrypt\FileCipher', get_declared_classes())) {
//     require_once "FileCipher.php";
// }
use DataCrypt\FileCipher;

class FileCrypt
{
    public static function encrypt($file, $output)
    {
        $key = (new \Zend\Session\Container('application_vars'))->storage['secure_file_key'];

        $fileCipher = new FileCipher;

        $fileCipher->setKey($key);

        return $fileCipher->encrypt($file, $output);
    }

    public static function decrypt($file, $output)
    {
        $key = (new \Zend\Session\Container('application_vars'))->storage['secure_file_key'];

        $fileCipher = new FileCipher;

        $fileCipher->setKey($key);

        return $fileCipher->decrypt($file, $output);
    }
}