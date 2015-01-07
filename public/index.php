<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */

if(function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime())
    set_magic_quotes_runtime(false);

if(get_magic_quotes_gpc()) {
    array_stripslashes($_POST);
    array_stripslashes($_GET);
    array_stripslashes($_COOKIES);
}

function array_stripslashes(&$array) {
    if(is_array($array))
        while(list($key) = each($array))
            if(is_array($array[$key]))
                array_stripslashes($array[$key]);
            else
                $array[$key] = stripslashes($array[$key]);
}

chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
