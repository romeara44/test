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

class AssessmentBusinessAssociateLocationTable implements ServiceLocatorAwareInterface
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

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getAbal($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('abal_id = ' . $id);
        $select->where('abal_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function checkStep($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('abal_a_id = ' . (int) $aId);
        $select->where('abal_adr_id = ' . (int) $adrId);
        $select->where('abal_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return true;
    }

    public function getAbalsByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('abal_a_id = ' . (int) $aId);
        $select->where('abal_adr_id = ' . (int) $adrId);
        $select->where('abal_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function saveAbal(AssessmentBusinessAssociateLocation $abal)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id = isset($abal->abal_id) ? $abal->abal_id : 0;

        $data = array();

        if (!$id) {
            $data['abal_a_id'] = $abal->abal_a_id;
            $data['abal_adr_id'] = $abal->abal_adr_id;
        }
        $data['abal_ba_id'] = $abal->abal_ba_id;

        if ($id == 0) {
            $data['abal_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getAbal($id)) {
                $data['abal_update_u_id'] = $identity['u_id'];
                $data['abal_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('abal_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteAbal($id)
    {
        $data['abal_id'] = $id;
        $data['abal_active'] = 0;
        $this->tableGateway->update($data, array('abal_id' => $id));

        return true;
    }
}