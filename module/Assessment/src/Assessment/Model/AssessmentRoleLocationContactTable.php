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

class AssessmentRoleLocationContactTable implements ServiceLocatorAwareInterface
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

    public function getArlc($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('arlc_id = ' . $id);
        $select->where('arlc_active = 1');

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
        $select->where('arlc_a_id = ' . (int) $aId);
        $select->where('arlc_adr_id = ' . (int) $adrId);
        $select->where('arlc_u_id <> 0');
        $select->where('arlc_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return count($resultSet) == 6 ? true : false;
    }

    public function getArlcByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('arlc_a_id = ' . (int) $aId);
        $select->where('arlc_adr_id = ' . (int) $adrId);
        $select->where('arlc_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->arlc_ar_id] = $rs->arlc_u_id;
        }

        return $ret;
    }

    public function getArlcsByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('arlc_a_id = ' . (int) $aId);
        $select->where('arlc_adr_id = ' . (int) $adrId);
        $select->where('arlc_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getArlcIdByAttrs($aId, $adrId, $arId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('arlc_a_id = ' . (int) $aId);
        $select->where('arlc_adr_id = ' . (int) $adrId);
        $select->where('arlc_ar_id = ' . (int) $arId);
        $select->where('arlc_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return 0;
        }

        return $row->arlc_id;
    }

    public function getComplianceOfficers($aId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('arlc_a_id = ' . (int) $aId);
        $select->where('arlc_ar_id = ' . (int) 5);
        $select->where('arlc_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $uIds = array(0);
        foreach ($resultSet as $rs) {
            $uIds[] = $rs->arlc_u_id;
        }


        return $uIds;
    }

    public function saveArlc(AssessmentRoleLocationContact $arlc)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id = $this->getArlcIdByAttrs($arlc->arlc_a_id, $arlc->arlc_adr_id, $arlc->arlc_ar_id);
        $id ? $data['arlc_id'] = $id : 0;

        $data = array();

        $data['arlc_a_id'] = $arlc->arlc_a_id;
        $data['arlc_adr_id'] = $arlc->arlc_adr_id;
        $data['arlc_ar_id'] = $arlc->arlc_ar_id;
        $data['arlc_u_id'] = $arlc->arlc_u_id;

        if (!$id) {
            $data['arlc_create_u_id'] = $identity['u_id'];
        } else {
            $data['arlc_update_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
            //$this->_sendEmailConfirmation($id);
        } else {
            if ($this->getArlc($id)) {
                $data['arlc_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('arlc_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }


}