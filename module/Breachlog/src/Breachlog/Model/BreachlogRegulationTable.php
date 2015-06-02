<?php
namespace Breachlog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;

use Zend\Db\Sql\Expression;

class BreachlogRegulationTable implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getBreachlogRegulations()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();

        $resultSet = $this->tableGateway->selectWith($select);

        $regulations = array();
        
        foreach ($resultSet as $rs) {
            $types[$rs->blrg_id] = $rs;
        }

        return $regulations;
    }

    public function deleteByBreachlogId($blId)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('breach_logs_regulations')->where(array('blrg_bl_id' => $blId));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        return true;
   }

    public function saveBreachlogRegulation($data)
    {
        $this->tableGateway->insert($data);

        return $this->tableGateway->lastInsertValue;;
    }

}