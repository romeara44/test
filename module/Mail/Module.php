<?php
namespace Mail;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(

                'Mail\Model\ErrormailrecipientsTable' =>  function($sm) {
                        $tableGateway = $sm->get('ErrormailrecipientsTableGateway');
                        $table = new \Mail\Model\ErrormailrecipientsTable($tableGateway);
                        return $table;
                },
                'ErrormailrecipientsTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('error_mail_recipients', $dbAdapter, null, $resultSetPrototype);
                },
                'Mail\Model\MailtemplateTable' =>  function($sm) {
                        $tableGateway = $sm->get('MailtemplateTableGateway');
                        $table = new \Mail\Model\MailtemplateTable($tableGateway);
                        return $table;
                },
                'MailtemplateTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('mail_templates', $dbAdapter, null, $resultSetPrototype);
                },
                'Mail\Model\MailsentTable' =>  function($sm) {
                        $tableGateway = $sm->get('MailsentTableGateway');
                        $table = new \Mail\Model\MailsentTable($tableGateway);
                        return $table;
                },
                'MailsentTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('mail_sent', $dbAdapter, null, $resultSetPrototype);
                }

            ),
        );
    }
}
