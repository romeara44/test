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

use Zend\Db\Sql\Expression;

class PhysicalsecuritychangeTable implements ServiceLocatorAwareInterface
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

    public function getPhysicalsecuritychanges($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();
            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('psc_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Physicalsecuritychange());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('psc_active = ' . $roleFilter);
            }

            if ($searchValue !== null) {
                $select->where('(c_name LIKE "%' . $searchValue . '%")');
            }

            $select->columns(array('psc_id',
                               '_company_name' => new \Zend\Db\Sql\Expression('c_name'),
                               '_location' => new \Zend\Db\Sql\Expression('CONCAT("Location ", psc_adr_id)'),
                               //'_location' => new \Zend\Db\Sql\Expression('adr_name'),
                               '_type' => new \Zend\Db\Sql\Expression('psc_change_type'),
                               'psc_create_date',
                               'psc_active'
                               )
                        );
            $select->join(array('c' => 'companies'), 'psc_c_id = c_id', array(), 'left');
            //$select->join(array('ai' => 'addresses_items'), 'psc_adr_id = cadr_id', array(), 'left');
            //$select->join(array('a' => 'addresses'), 'cadr_adr_id = adr_id', array(), 'left');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $select->where("psc_create_u_id = " . $identity['u_id']);
            //$select->where('adr_active = 1');

            $select->group('psc_id');

            $paginator = new Paginator($paginatorAdapter);
// print_r($select->getSqlString());exit;
            return $paginator;
        }
        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    public function getPhysicalsecuritychange($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('psc_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }

    public function savePhysicalsecuritychange(Physicalsecuritychange $psc)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'psc_c_id'     => $psc->psc_c_id,
            'psc_adr_id'          => $psc->psc_adr_id,
            'psc_change_type'         => $psc->psc_change_type,
            'psc_active' => $psc->psc_active,
        );

        $id = (int) $psc->psc_id;

        if ($id == 0) {
            $data['psc_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_PSC, $id);
        } else {
            if ($this->getPhysicalsecuritychange($id)) {
                $this->tableGateway->update($data, array('psc_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_PSC, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }        

        return $id;
    }
}