<?php
namespace Breachlog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Admin\Model\User;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Breachlog\Model\Breachremediationplanaction;
use DataCrypt\DbCrypt;

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

    private function _getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));

        return $authService->getIdentity();
    }

    public function getBreachremediationplanactions($brpId = 0)
    {
        $brpId  = (int) $brpId;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brpa_id'                 => 'brpa_id',
                               'brpa_brp_id'             => 'brpa_brp_id',
                               'brpa_task'               => DbCrypt::decryptField('brpa_task'),
                               'brpa_action_plan'        => DbCrypt::decryptField('brpa_action_plan'),
                               'brpa_status'             => DbCrypt::decryptField('brpa_status'),
                               'brpa_contact_u_id'       => 'brpa_contact_u_id',
                               'brpa_approver_u_id'      => 'brpa_approver_u_id',
                               'brpa_target_date'        => DbCrypt::decryptField('brpa_target_date'),
                               'brpa_create_date'        => DbCrypt::decryptField('brpa_create_date'),
                               'brpa_latest_action_date' => DbCrypt::decryptField('brpa_latest_action_date'),
                               'brpa_active'             => 'brpa_active'
                              )
                            );

        $select->where('brpa_brp_id = ' . $brpId);
        $select->where('brpa_active = 1');

        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('brpa_contact_u_id = u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_brpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(' . DbCrypt::decryptField('brpa_target_date', false) . ', "%c/%e/%Y")')), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('brpa_approver_u_id = u2.u_id'), array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function encryptItems()
    {
        $identity = $this->_getIdentity();

        $select = $this->tableGateway->getSql()->select();

        $resultSet = $this->tableGateway->selectWith($select);

        foreach ($resultSet as $brpa) {
            $data = array(
              'brpa_task'               => DbCrypt::encryptValue($brpa->brpa_task),
              'brpa_action_plan'        => DbCrypt::encryptValue($brpa->brpa_action_plan),
              'brpa_status'             => DbCrypt::encryptValue($brpa->brpa_status),
              'brpa_target_date'        => DbCrypt::encryptValue($brpa->brpa_target_date),
              'brpa_create_date'        => DbCrypt::encryptValue($brpa->brpa_create_date),
              'brpa_latest_action_date' => DbCrypt::encryptValue($brpa->brpa_latest_action_date)
          );

          $this->tableGateway->update($data, array('brpa_id' => $brpa->brpa_id));
        }

        return true;
    }

    public function getBreachremediationplanaction($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brpa_id'                 => 'brpa_id',
                               'brpa_brp_id'             => 'brpa_brp_id',
                               'brpa_task'               => DbCrypt::decryptField('brpa_task'),
                               'brpa_action_plan'        => DbCrypt::decryptField('brpa_action_plan'),
                               'brpa_status'             => DbCrypt::decryptField('brpa_status'),
                               'brpa_contact_u_id'       => 'brpa_contact_u_id',
                               'brpa_approver_u_id'      => 'brpa_approver_u_id',
                               'brpa_target_date'        => DbCrypt::decryptField('brpa_target_date'),
                               'brpa_create_date'        => DbCrypt::decryptField('brpa_create_date'),
                               'brpa_latest_action_date' => DbCrypt::decryptField('brpa_latest_action_date'),
                               'brpa_active'             => 'brpa_active'
                              )
                            );

        $select->where('brpa_id = ' . $id);
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('brpa_contact_u_id = u_id'), array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_brpa_target_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(' . DbCrypt::decryptField('brpa_target_date', false) . ', "%m/%d/%Y")')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getBreachRemediationPlanAnactionsForReportPage($id, $status = null)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('_id'                  => 'brpa_id',
                                '_c_name'             => new \Zend\Db\Sql\Expression('c_name'),
                                '_type'               => new \Zend\Db\Sql\Expression('IF(true , "remediation" ,0)'),
                                '_status'             => DbCrypt::decryptField('brpa_status'),
                                '_task'               => DbCrypt::decryptField('brpa_task'),
                                '_approver_name'      => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)'),
                                '_contact_name'       => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'),
                                '_parent_id'          => new \Zend\Db\Sql\Expression('brpa_brp_id'),
                                '_latest_action_date' => new \Zend\Db\Sql\Expression('IF(brpa_latest_action_date, ' . DbCrypt::decryptField('brpa_latest_action_date', false) . ', DATE_FORMAT(' . DbCrypt::decryptField('brp.brp_incident_date', false) . ', "%Y-%m-%d"))')
                                )
                            );

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $select->where('brp_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('brp_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $select->where('brp_c_id = ' . $identity['u_company_id']);
        }


        $select->where('brp_active = 1');
        $select->where('brpa_brp_id = ' . $id);

        if($status !== null && $status !== '') {
            $select->where(DbCrypt::decryptField('brpa_status', false) . ' = ' . $status);
        }
        
        $select->join(array('brp' => 'breach_remediation_plans'), new \Zend\Db\Sql\Expression('brpa_brp_id = brp.brp_id'), array(), 'inner');
        $select->join(array('c' => 'companies'), 'brp_c_id = c_id', array(), 'left');
        $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('brpa_contact_u_id = u_id'), array(), 'left');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('brpa_approver_u_id = u2.u_id'), array(), 'left');

        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            new ResultSet()
        );

        $select->order('_latest_action_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function saveBreachremediationplanaction(Breachremediationplanaction $brpa)
    {
        $data = array(
            'brpa_brp_id'        => $brpa->brpa_brp_id,
            'brpa_task'          => DbCrypt::encryptValue($brpa->brpa_task),
            'brpa_action_plan'   => DbCrypt::encryptValue($brpa->brpa_action_plan),
            'brpa_status'        => DbCrypt::encryptValue($brpa->brpa_status),
            'brpa_contact_u_id'  => $brpa->brpa_contact_u_id,
            'brpa_approver_u_id' => $brpa->brpa_approver_u_id,
            'brpa_target_date'   => DbCrypt::encryptValue($brpa->brpa_target_date),
        );

        $id = (int) $brpa->brpa_id;

        if ($brpa->brpa_task == '') {
            unset($data['brpa_task']);
        }

        if ($id == 0) {
            $data['brpa_create_date'] = DbCrypt::encryptValue(date('Y-m-d H:i:s'));

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($brpaAction = $this->getBreachremediationplanaction($id)) {
                if($brpaAction->brpa_status !== $data['brpa_status']) {
                    $data['brpa_latest_action_date'] = DbCrypt::encryptValue(date('Y-m-d H:i:s'));
                }
                $data['brpa_status'] = DbCrypt::encryptValue($brpa->brpa_status);
                //if ($brpa->brpa_status == Breachremediationplanaction::STATUS_PENDING_APPROVAL) {
                    //$this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'pendingapproval', 'brpaId' => $id));
                //}
                $this->tableGateway->update($data, array('brpa_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteBreachremediationplanaction($id)
    {
        $data['brpa_id']     = $id;
        $data['brpa_active'] = 0;

        $this->tableGateway->update($data, array('brpa_id' => $id));

        return true;
    }
}