<?php
namespace Breachlog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Breachlog\Model\Breachremediationplanaction;

class BreachremediationplanactionTable implements ServiceLocatorAwareInterface
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

    public function getBreachremediationplanactions($brpId = 0)
    {
        $brpId  = (int) $brpId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('brpa_brp_id = ' . $brpId);
        $select->where('brpa_active = 1');

        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('brpa_contact_u_id = u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_brpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(brpa_target_date, "%c/%e/%Y")')), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('brpa_approver_u_id = u2.u_id'), array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getBreachremediationplanaction($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('brpa_id = ' . $id);
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('brpa_contact_u_id = u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_brpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(brpa_target_date, "m/d/y")')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveBreachremediationplanaction(Breachremediationplanaction $brpa)
    {
        $data = array(
            'brpa_brp_id' => $brpa->brpa_brp_id,
            'brpa_task' => $brpa->brpa_task,
            'brpa_action_plan' => $brpa->brpa_action_plan,
            'brpa_status' => $brpa->brpa_status,
            'brpa_contact_u_id' => $brpa->brpa_contact_u_id,
            'brpa_approver_u_id' => $brpa->brpa_approver_u_id,
            'brpa_target_date' => $brpa->brpa_target_date,
        );

        $id = (int) $brpa->brpa_id;

        if ($brpa->brpa_task == '') {
            unset($data['brpa_task']);
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($brpaAction = $this->getBreachremediationplanaction($id)) {

                if ($brpa->brpa_status == Breachremediationplanaction::STATUS_PENDING_APPROVAL) {
                    $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'pendingapproval', 'brpaId' => $id));
                }
                $this->tableGateway->update($data, array('brpa_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteBreachremediationplanaction($id)
    {
        $data['brpa_id'] = $id;
        $data['brpa_active'] = 0;
        $this->tableGateway->update($data, array('brpa_id' => $id));

        return true;
    }
}