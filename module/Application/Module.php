<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Model\ProvinceTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $translator = $e->getApplication()->getServiceManager()->get('translator');
        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);

				//handle the dispatch error (exception) 
				$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'));
				//handle the view render error (exception) 
				$eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'handleError'));
    }
		public function handleError(MvcEvent $e)
		{
			//get the exception
			$exception = $e->getParam('exception');

			$to = "jim.manton@skybeam.com"; // this is your Email address
			$from = "jim.manton@skybeam.com"; // this is the sender's Email address
			$first_name = 'jim';
			$last_name = 'manton';
			$subject = "Carosh global error handling.";
			$message = $first_name . " " . $last_name . " this global error was returned:" . "\n\n" . $exception->getMessage();

			$headers = "From:" . $from;

			$mail = new Mail\Message();
			$mail->setFrom('postmaster@click5dev7.com', 'HIPAA Suite');

			$html = new \Zend\Mime\Part($message);
			$html->type = 'text/html';
			$body = new \Zend\Mime\Message;
			$body->addPart($html);
			$mail->setBody($body);
			
			$mail->setSubject($subject);
			$options = new SmtpOptions();
			$options
					->setHost('smtp.sendgrid.net')
					->setConnectionClass('login')
					->setName('smtp.sendgrid.net')
					->setConnectionConfig(array(
							'auth' => 'login',
							'username' => 'HIPAASuite',
							'password' => '1948Box13',
							'ssl' => 'tls',
							'port' => 587
					));
			$transport = new SmtpTransport();
			$transport->setOptions($options);
			$mail->addTo($to, 'jim manton');
			$transport->send($mail);
		}
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

                'Application\Model\FilesTable' =>  function($sm) {
                    $tableGateway = $sm->get('FilesTableGateway');
                    $table = new \Application\Model\FilesTable($tableGateway);
                    return $table;
                },
                'FilesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('files', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\LogsTable' =>  function($sm) {
                    $tableGateway = $sm->get('LogsTableGateway');
                    $table = new \Application\Model\LogsTable($tableGateway);
                    return $table;
                },
                'LogsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('logs', $dbAdapter, null, $resultSetPrototype);
                },
                'Sitesetting\Model\SitesettingTable' =>  function($sm) {
                    $tableGateway = $sm->get('SitesettingTableGateway');
                    $table = new \Sitesetting\Model\SitesettingTable($tableGateway);
                    return $table;
                },
                'SitesettingTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Sitesetting\Model\Sitesetting());
                    return new TableGateway('site_settings', $dbAdapter, null, $resultSetPrototype);
                }


            ),
        );
    }
}
