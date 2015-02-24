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
                $select->where('(tl_regulation LIKE "%' . $searchValue . '%" OR  tl_attendees LIKE "%' . $searchValue . '%")');
            }

            $select->join(array('tlt' => 'training_log_types'), 'tl_tlt_id = tlt_id', array('_tlt_name' => 'tlt_name'), 'inner');
            $select->join(array('n' => 'notes'), new \Zend\Db\Sql\Expression('tl_id = n.note_item_id'), array('_tl_comment' => new \Zend\Db\Sql\Expression('n.note_text')), 'left');
            $select->join(array('n2' => 'notes'), new \Zend\Db\Sql\Expression('tl_id = n2.note_item_id AND n2.note_text =""'), array('_tl_attachment' => new \Zend\Db\Sql\Expression('n2.note_id')), 'left');
            $select->join(array('tlrg' => 'training_logs_regulations'), new \Zend\Db\Sql\Expression('tl_id = tlrg.tlrg_tl_id'), array('_tl_tlrg_rg_id' => new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id')), 'left');
            $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('tlrg.tlrg_rg_id = rg.rg_id'), array('_tl_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'left');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $select->where("(n.note_text !='' OR n.note_text IS NULL)");
            $select->group('tl_id');

            $paginator = new Paginator($paginatorAdapter);

 // print_r($select->getSqlString());exit;
            return $paginator;
        }
        $resultSet = $this->tableGateway->select();

        return $resultSet;
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
        
        foreach ($regulations as $rs) {
            $row->_tl_cur_regulations[$rs->_tl_tlrg_id] = $rs->_tl_tlrg_rg_id;
        }
        return $row;
    }

    public function saveTraininglog(Traininglog $traininglog)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'tl_tlt_id'         => $traininglog->tl_tlt_id,
            'tl_conducted_date' => $traininglog->tl_conducted_date,
            'tl_hire_date'      => $traininglog->tl_hire_date,
            'tl_trainer'        => $traininglog->tl_trainer,
            'tl_attendees'      => $traininglog->tl_attendees
        );

        $id = (int) $traininglog->tl_id;

        if ($id == 0) {
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
            foreach ($traininglog->_tl_cur_regulations as $key => $_tl_cur_regulation) {
                if($_tl_cur_regulation == -1 && $traininglog->_regulation) {
                    $regulationData = array( 'rg_pp_name'     => $traininglog->_regulation[-1]['rg_pp_name']
                                           , 'rg_pp_number'   => $traininglog->_regulation[-1]['rg_pp_number']
                                           , 'rg_number'      => $traininglog->_regulation[-1]['rg_number']
                                           , 'rg_description' => $traininglog->_regulation[-1]['rg_description']
                                           , 'rg_u_owner_id'  => $identity['u_id']
                                           );
// print_r($regulationData);exit;
                    $rgId = $this->getServiceLocator()->get('Traininglog\Model\RegulationTable')->saveRegulation($regulationData);

                    $traininglogRegulationTable->saveTraininglogRegulation(array('tlrg_tl_id' => $id, 'tlrg_rg_id' => $rgId));
                } else {
                    $traininglogRegulationTable->saveTraininglogRegulation(array('tlrg_tl_id' => $id, 'tlrg_rg_id' => $_tl_cur_regulation));
                }
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

    public function unarchiveTraininglog($id)
    {
        $data['tl_id'] = $id;
        $data['tl_active'] = 1;
        $this->tableGateway->update($data, array('tl_id' => $id));

        return true;
    }


}