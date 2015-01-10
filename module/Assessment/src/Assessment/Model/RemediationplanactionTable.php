<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Assessment\Model\Remediationplanaction;

class RemediationplanactionTable implements ServiceLocatorAwareInterface
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

    public function getRemediationplanactions($rpId = 0, $orderBy = null, $order = null)
    {
        $rpId  = (int) $rpId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rpa_rp_id = ' . $rpId);
        $select->where('rpa_active = 1');

        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('rpa_contact_u_id = u_id'), array('_rpa_risk_level_sort' => new \Zend\Db\Sql\Expression('IF(rpa_risk_level = 3, -1, rpa_risk_level)'), '_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_rpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rpa_target_date, "%c/%e/%Y")')), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('rpa_approver_u_id = u2.u_id'), array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('adr' => 'addresses'), new \Zend\Db\Sql\Expression('adr_id = rpa_adr_id'), array('_location_name' => new \Zend\Db\Sql\Expression('adr_name')), 'left');

        $orderStr = '-rpa_adr_id DESC, _rpa_risk_level_sort DESC';
        
        $order = $order ? $order : 'ASC';
        
        if ($orderBy) {
            $orderStr .= ', ' . $orderBy . ' ' . $order;
        }
        
        $select->order(new \Zend\Db\Sql\Expression($orderStr));

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getRemediationplanaction($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rpa_id = ' . $id);
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('rpa_contact_u_id = u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveRemediationplanaction(Remediationplanaction $rpa, $isEdit = false)
    {
        $data = array(
            'rpa_rp_id' => $rpa->rpa_rp_id,
            'rpa_threat' => $rpa->rpa_threat,
            'rpa_action_plan' => $rpa->rpa_action_plan,
            'rpa_risk_level' => $rpa->rpa_risk_level,
            'rpa_status' => $rpa->rpa_status,
            'rpa_contact_u_id' => $rpa->rpa_contact_u_id,
            'rpa_approver_u_id' => $rpa->rpa_approver_u_id,
            'rpa_target_date' => $rpa->rpa_target_date,
        );

        if ($rpa->rpa_adr_id) {
            $data['rpa_adr_id'] = $rpa->rpa_adr_id;
        }

        if ($rpa->rpa_aqc_id) {
            $data['rpa_aqc_id'] = $rpa->rpa_aqc_id;
        }
        if ($rpa->rpa_policy) {
            $data['rpa_policy'] = $rpa->rpa_policy;
        }

        if (!$isEdit) {
            $data['rpa_risk_score'] = $rpa->rpa_risk_score;
            $data['rpa_risk_level'] = $rpa->rpa_risk_level;
        }
        if ($rpa->rpa_action_plan == '') {
            unset($data['rpa_action_plan']);
        }
        if ($rpa->rpa_threat == '') {
            unset($data['rpa_threat']);
        }

        $id = (int) $rpa->rpa_id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($rpaAction = $this->getRemediationplanaction($id)) {

                if ($rpa->rpa_status == Remediationplanaction::STATUS_PENDING_APPROVAL) {
                    $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'pendingapproval', 'rpaId' => $id));
                }
                $this->tableGateway->update($data, array('rpa_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteRemediationplanaction($id)
    {
        $data['rpa_id'] = $id;
        $data['rpa_active'] = 0;
        $this->tableGateway->update($data, array('rpa_id' => $id));

        return true;
    }
}