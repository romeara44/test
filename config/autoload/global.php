<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In contest, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=rockyhil_hipaa;host=localhost',
        'user'           => 'root',
        'password'       => 'Orriginalp1zza',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'pl_PL'
    ),
    'application_vars' => array(
        'register_email' => 'Registration@Carosh.com',
        'module_access_code_expiration' => 60 * 20,
        'secure_db_key_file' => ROOT_PATH . '/../c147572b91c6719b26c5.dbkey',
        'secure_file_key_file' => ROOT_PATH . '/../ccb3177cdb1f576a6d31.filekey',
        'assessment_interview_autosave_interval' => 1,
    )
);

