<?php
namespace Traininglog\Model;

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

class TraininglogTable implements ServiceLocatorAwareInterface
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

    public function getTraininglogs($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();
            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('tl_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Traininglog());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('tl_active = ' . $roleFilter);
            }

            if ($searchValue !== null) {
                $select->where('(tl_attendees LIKE "%' . $searchValue . '%" OR rg.rg_pp_name LIKE "%' . $searchValue . '%" OR rg.rg_pp_number LIKE "%' . $searchValue . '%" OR rg.rg_number LIKE "%' . $searchValue . '%" OR tl_title LIKE "%' . $searchValue . '%")');
            }
            $select->columns(array('*', '_tl_trainer_name' => new \Zend\Db\Sql\Expression('IF(tr.tr_name IS NULL, CONCAT(u.u_firstname, " ", u.u_lastname), tr.tr_name)')));
            $select->join(array('tlt' => 'training_log_types'), 'tl_tlt_id = tlt_id', array('_tlt_name' => 'tlt_name'), 'inner');
            $select->join(array('n' => 'notes'), new \Zend\Db\Sql\Expression('tl_id = n.note_item_id AND n.note_item_type = ' . \Note\Model\Note::NOTE_TLC), array('_tl_comment' => new \Zend\Db\Sql\Expression('n.note_text')), 'left');
            $select->join(array('n2' => 'notes'), new \Zend\Db\Sql\Expression('tl_id = n2.note_item_id AND n2.note_item_type = ' . \Note\Model\Note::NOTE_TLT), array('_tl_attachment' => new \Zend\Db\Sql\Expression('n2.note_id')), 'left');
            $select->join(array('tlrg' => 'training_logs_regulations'), new \Zend\Db\Sql\Expression('tl_id = tlrg.tlrg_tl_id'), array('_tl_tlrg_rg_id' => new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id')), 'left');
            $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id = rg.rg_id'), array('_tl_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'left');
            $select->join(array('tr' => 'trainers'), new \Zend\Db\Sql\Expression('CONCAT(tl_trainer_type, "_", tl_trainer_id) = CONCAT("trainer", "_", tr.tr_id)'), array(), 'left');
            $select->join(array('u' => 'users'), new \Zend\Db\Sql\Expression('CONCAT(tl_trainer_type, "_", tl_trainer_id) = CONCAT("user", "_", u.u_id)'), array(), 'left');
            $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('tl_create_u_id = u2.u_id'), array(), 'left');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }
            if($identity['u_company_id']) {
                $select->where("u2.u_company_id = " . $identity['u_company_id']);
            } else {
                $select->where("u2.u_company_id IS NULL ");
            }
            $select->group('tl_id');

            $paginator = new Paginator($paginatorAdapter);
// print_r($select->getSqlString());exit;
            return $paginator;
        }
        $resultSet = $this->tableGateway->select();

        foreach ($resultSet as $key => $trainingLog) {
            if($trainingLog->tl_trainer_type == 'trainer') {
                $trainer = $this->getServiceLocator()->get('Traininglog\Model\TrainerTable')->getTrainer($trainingLog->tl_trainer_id);
                $resultSet[$key]->_trainer_name = $trainer->tr_name;
            } else {
                $user = $this->getServiceLocator()->get('Traininglog\Model\TrainerTable')->getTrainer($trainingLog->tl_trainer_id);
                $resultSet[$key]->_trainer_name = $user->u_firstname . ' ' . $user->u_lastname;
            }
        }

        return $resultSet;
    }


    public function getTraininglogsForReporting($searchValue = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('tl_active = 1');
        }
        
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Traininglog());
        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );

        if ($searchValue !== null) {
            $select->where('(rg.rg_number LIKE "%' . $searchValue . '%" OR rg.rg_description LIKE "%' . $searchValue . '%")');
        }
        $select->columns(array('tl_id', 'tl_title', 'tl_conducted_date'));
        $select->join(array('tlrg' => 'training_logs_regulations'), new \Zend\Db\Sql\Expression('tl_id = tlrg.tlrg_tl_id'), array('_tl_tlrg_rg_id' => new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id')), 'inner');
        $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id = rg.rg_id'), array('_tl_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'inner');
        $select->join(array('u2' => 'users'), new \Zend\Db\Sql\Expression('tl_create_u_id = u2.u_id'), array(), 'inner');

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            if($identity['u_company_id']) {
                $select->where("u2.u_company_id = " . $identity['u_company_id']);
            } else {
                $select->where("u2.u_company_id IS NULL ");
            }
        }

        $select->group('tl_id');
        $select->order('tl_conducted_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function getTraininglog($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('tl_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $row->_tl_cur_regulations = array();

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('tlrg' => 'training_logs_regulations'), new \Zend\Db\Sql\Expression('tl_id = tlrg.tlrg_tl_id'), array('_tl_tlrg_id' => new \Zend\Db\Sql\Expression('tlrg.tlrg_id'), '_tl_tlrg_rg_id' => new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id')), 'inner');
        $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id = rg.rg_id'), array('_tl_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'inner');
        $select->where("tl_id =" . $id);

        $regulations = $this->tableGateway->selectWith($select);

        $row->_tl_trainer = $row->tl_trainer_type . '_' . $row->tl_trainer_id;

        foreach ($regulations as $rs) {
            $row->_tl_cur_regulations[$rs->_tl_tlrg_id] = $rs->_tl_tlrg_rg_id;
        }
        return $row;
    }

    public function getTrainers()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $result = array();

        $users    = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($identity['u_company_id']);
        $trainers = $this->getServiceLocator()->get('Traininglog\Model\TrainerTable')->getTrainersByCompany($identity['u_company_id']);

        foreach ($users as $key => $user) {
            $result['user_' . $user->u_id] = $user->u_firstname . ' ' . $user->u_lastname;
        }

        if($trainers->count() > 0) {
            foreach ($trainers as $key => $trainer) {
                $result['trainer_' . $trainer->tr_id] = $trainer->tr_name;
            }

        }

        natcasesort($result);

        return $result;
    }

    public function saveTraininglog(Traininglog $traininglog)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'tl_title'          => $traininglog->tl_title,
            'tl_tlt_id'         => $traininglog->tl_tlt_id,
            'tl_conducted_date' => $traininglog->tl_conducted_date,
            'tl_hire_date'      => $traininglog->tl_hire_date,
            'tl_attendees'      => $traininglog->tl_attendees
        );

        if($traininglog->_tl_trainer != '-1') {
            $data['tl_trainer_id']   = $traininglog->tl_trainer_id;
            $data['tl_trainer_type'] = $traininglog->tl_trainer_type;
        }

        $id = (int) $traininglog->tl_id;

        if ($id == 0) {
            $data['tl_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_TL, $id);
        } else {
            if ($this->getTraininglog($id)) {
                $this->tableGateway->update($data, array('tl_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_TL, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        $traininglogRegulationTable = $this->getServiceLocator()->get('Traininglog\Model\TraininglogRegulationTable');

        $traininglogRegulationTable->deleteByTraininglogId($id);

        if($traininglog->_tl_cur_regulations) {
            foreach ($traininglog->_tl_cur_regulations as $_tl_cur_regulation) {
                if($_tl_cur_regulation == -1 && $traininglog->_regulation) {
                    $regulationData = array( 'rg_pp_name'     => $traininglog->_regulation[-1]['rg_pp_name']
                                           , 'rg_pp_number'   => $traininglog->_regulation[-1]['rg_pp_number']
                                           , 'rg_number'      => $traininglog->_regulation[-1]['rg_number']
                                           , 'rg_description' => $traininglog->_regulation[-1]['rg_description']
                                           , 'rg_u_owner_id'  => $identity['u_id']
                                           );

                    $rgId = $this->getServiceLocator()->get('Traininglog\Model\RegulationTable')->saveRegulation($regulationData);

                    $traininglogRegulationTable->saveTraininglogRegulation(array('tlrg_tl_id' => $id, 'tlrg_rg_id' => $rgId));
                } else {
                    $traininglogRegulationTable->saveTraininglogRegulation(array('tlrg_tl_id' => $id, 'tlrg_rg_id' => $_tl_cur_regulation));
                }
            }
        }

        if($traininglog->_tl_trainer == '-1' && $traininglog->_tl_trainer_name) {
            $trainerTable = $this->getServiceLocator()->get('Traininglog\Model\TrainerTable');
            $trainerId = $trainerTable->saveTrainer(array('tr_company_id' => $identity['u_company_id'], 'tr_name' => $traininglog->_tl_trainer_name));
            if($trainerId) {
                 $this->tableGateway->update(array('tl_trainer_id' => $trainerId, 'tl_trainer_type' => 'trainer'), array('tl_id' => $id));
            }
        }

        if($traininglog->tl_tlt_id == '-1' && $traininglog->_tl_type_name) {
            $typeId = $this->getServiceLocator()->get('Traininglog\Model\TraininglogtypeTable')->saveTraininglogtype(array('tlt_company_id' => $identity['u_company_id'], 'tlt_name' => $traininglog->_tl_type_name));
            if($typeId) {
                 $this->tableGateway->update(array('tl_tlt_id' => $typeId), array('tl_id' => $id));
            }
        }

        return $id;
    }

    public function deleteTraininglog($id)
    {
        $data['tl_id'] = $id;
        $data['tl_active'] = 0;
        $this->tableGateway->update($data, array('tl_id' => $id));
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_TL, $id);
        return true;
    }

    public function deleteTraininglogsByCompanyId($cId, $value = 0)
    {
        $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($cId);
        
        if($users) {
            foreach ($users as $user) {
                $data['tl_active'] = $value;
                $this->tableGateway->update($data, array('tl_create_u_id' => $user->u_id));
            }
        }

        return true;
    }

    public function unarchiveTraininglog($id)
    {
        $data['tl_id'] = $id;
        $data['tl_active'] = 1;
        $this->tableGateway->update($data, array('tl_id' => $id));

        return true;
    }


}