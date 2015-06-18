<?php
namespace Assessment\Model;

use Admin\Model\User;
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

        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('rpa_contact_u_id = u_id'), array('_rpa_risk_level_sort' => new \Zend\Db\Sql\Expression('IF(rpa_risk_level = 3, -1, rpa_risk_level)'), '_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_rpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rpa_target_date, "%m/%d/%Y")')), 'left');
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

    public function getRemediationPlanActionsForReporting($searchValue = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = new Select('remediation_plans_actions');

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Remediationplanaction());
        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );

        if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            $select->where('(rp_active = 1 || rp_active = 0)');
        } else {
            $select->where('rpa_active = 1');

            if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                $select->where('rp_consultant_u_id = ' . $identity['u_id']);
            } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                $ids[] = $identity['u_id'];
                $select->where('rp_consultant_u_id IN (' . implode(',', $ids) . ')');
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $select->where('rp_c_id = ' . $identity['u_company_id']);
            }
        }

        if ($searchValue !== null) {
            $select->where('(rpa_threat LIKE "%' . $searchValue . '%" OR rpa_policy LIKE "%' . $searchValue . '%")');
        }

        $select->join(array('rp' => 'remediation_plans'), new \Zend\Db\Sql\Expression('rpa_rp_id = rp.rp_id'), array(), 'inner');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('rpa_approver_u_id = u2.u_id'), array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');

        $select->group(array('rpa_rp_id', 'rpa_id'));
        $select->order('rpa_target_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function getRemediationPlanActionsForReportPage($rpId = 0, $status = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $rpId  = (int) $rpId;

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('_id'        => 'rpa_id',
                                '_c_name'    => new \Zend\Db\Sql\Expression('c_name'),
                                '_type'      => new \Zend\Db\Sql\Expression('IF(true , "remediation" ,0)'),
                                '_status'    => 'rpa_status',
                                '_task'      => 'rpa_threat',
                                '_approver_name' =>new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)'),
                                '_contact_name'      => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'),
                                '_parent_id'      => new \Zend\Db\Sql\Expression('rpa_rp_id'),
                                '_latest_action_date'      => new \Zend\Db\Sql\Expression('IF(rpa_latest_action_date, rpa_latest_action_date, IF(rp.rp_a_id , rp.rp_incident_date , rp.rp_remediation_date))')
                                )
                            );

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $select->where('rp_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('rp_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $select->where('rp_c_id = ' . $identity['u_company_id']);
        }
        

        $select->where('rpa_rp_id = ' . $rpId);
        $select->where('rpa_active = 1');

        $select->join(array('rp' => 'remediation_plans'), new \Zend\Db\Sql\Expression('rpa_rp_id = rp.rp_id'), array(), 'inner');
        $select->join(array('c' => 'companies'), 'rp_c_id = c_id', array(), 'left');
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('rpa_contact_u_id = u_id'), array(), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('rpa_approver_u_id = u2.u_id'), array(), 'left');
        
        if($status !== null) {
            $select->where('rpa_status = ' . $status);
        }

        $select->order('_latest_action_date DESC');

        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            new ResultSet()
        );

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function getRemediationplanaction($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rpa_id = ' . $id);
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('rpa_contact_u_id = u.u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_rpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rpa_target_date, "%m/%d/%Y")')), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('rpa_approver_u_id = u2.u_id'), array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');

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
                if($rpaAction->rpa_status !== $data['rpa_status']) {
                    $data['rpa_latest_action_date'] = date('Y-m-d H:i:s');
                }
                if ($rpa->rpa_status == Remediationplanaction::STATUS_PENDING_APPROVAL) {
                    // $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'pendingapproval', 'rpaId' => $id));
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