<?php
namespace Physicalsecuritychange\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;

use Zend\Db\Sql\Expression;

class PhysicalsecuritychangeitemTable implements ServiceLocatorAwareInterface
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

    public function getItems($id)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('psci_psc_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getItemsIds($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('psci_id'));

        $select->where('psci_psc_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $res = [];

        foreach ($resultSet as $value) {
            $res[] = $value->psci_id;
        }

        return $res;
    }

    public function getItem($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('psci_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }

    public function savePsci(Physicalsecuritychangeitem $psci)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $psci_date = \DateTime::createFromFormat('m/d/Y', $psci->psci_date);

        $data = array(
            'psci_psc_id'     => $psci->psci_psc_id,
            'psci_date'          => $psci_date->format('Y-m-d'),
            'psci_identification'         => $psci->psci_identification,
            'psci_reason' => $psci->psci_reason,
            'psci_person'          => $psci->psci_person,
            'psci_individual'         => $psci->psci_individual,
            'psci_comments' => $psci->psci_comments,
        );

        $id = (int) $psci->psci_id;

        if ($id == 0) {
            $data['psci_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
            if (!$id) return false;
            
        } else {
            if ($this->getItem($id)) {
                $this->tableGateway->update($data, array('psci_id' => $id));                
            } else {
                return false;
            }
        } 

        return $id;
    }

    public function deleteItem($id)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('physical_security_changes_items')->where(array('psci_id' => $id));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        return true;
   }
}