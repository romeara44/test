<?php
namespace Securityreminder\Model;

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

class SecurityreminderTable implements ServiceLocatorAwareInterface
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

    public function getSecurityreminders($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();
        
        if ($paginated) {
            $select = new Select('security_reminders');
            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('sr_active = 1');
            }

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Securityreminder());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('sr_active = ' . $roleFilter);
            }

            if ($searchValue !== null) {
                $select->where('(sr_regulation LIKE "%' . $searchValue . '%" OR  sr_developed_by LIKE "%' . $searchValue . '%")');
            }

            $select->join(array('dt' => 'distribution_types'), 'sr_dt_id = dt_id', array('_dt_type' => 'dt_type'), 'inner');
            $select->join(array('n' => 'notes'), new \Zend\Db\Sql\Expression('sr_id = n.note_item_id'), array('_sr_comment' => new \Zend\Db\Sql\Expression('n.note_text')), 'left');
            $select->join(array('n2' => 'notes'), new \Zend\Db\Sql\Expression('sr_id = n2.note_item_id AND n2.note_text =""'), array('_sr_attachment' => new \Zend\Db\Sql\Expression('n2.note_id')), 'left');
            $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('sr_create_u_id = u.u_id'), array(), 'left');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }
            if($identity['u_company_id']) {
                $select->where("u.u_company_id = " . $identity['u_company_id']);
            } else {
                $select->where("u.u_company_id IS NULL ");
            }

            $select->where("(n.note_text !='' OR n.note_text IS NULL)");
            $select->group('sr_id');
// print_r($select->getSqlString());exit;
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSecurityreminder($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('sr_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveSecurityreminder(Securityreminder $securityreminder)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'sr_dt_id'         => $securityreminder->sr_dt_id,
            'sr_launched_date' => $securityreminder->sr_launched_date,
            'sr_developed_by'  => $securityreminder->sr_developed_by,
            'sr_regulation'    => $securityreminder->sr_regulation,
        );

        $id = (int) $securityreminder->sr_id;

        if ($id == 0) {
            $data['sr_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_SR, $id);
        } else {
            if ($this->getSecurityreminder($id)) {
                $this->tableGateway->update($data, array('sr_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_SR, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteSecurityreminder($id)
    {
        $data['sr_id'] = $id;
        $data['sr_active'] = 0;
        $this->tableGateway->update($data, array('sr_id' => $id));
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_SR, $id);
        return true;
    }

    public function unarchiveSecurityreminder($id)
    {
        $data['sr_id'] = $id;
        $data['sr_active'] = 1;
        $this->tableGateway->update($data, array('sr_id' => $id));

        return true;
    }
}