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

class AssessmentInventoryLocationItemTable implements ServiceLocatorAwareInterface
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

    public function getAili($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('aili_id = ' . $id);
        $select->where('aili_active = 1');

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
        $select->where('aili_a_id = ' . (int) $aId);
        $select->where('aili_adr_id = ' . (int) $adrId);
        $select->where('aili_active = 1');
        $select->group('aili_ai_id');

        $resultSet = $this->tableGateway->selectWith($select);

        //$idAili = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->saveAilr($id, $pp, $request->getFiles());
        $files = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getAilrsByAssessment($aId, $adrId);

        $res = (count($resultSet) >= 1) || (count($files) >= 1) ? true : false;

        return $res;
    }

    public function getAiliByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aili_a_id = ' . (int) $aId);
        $select->where('aili_adr_id = ' . (int) $adrId);
        $select->where('aili_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->aili_ai_id][] = $rs;
        }

        return $ret;
    }

    public function getAilisByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aili_a_id = ' . (int) $aId);
        $select->where('aili_adr_id = ' . (int) $adrId);
        $select->where('aili_active = 1');

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

    public function saveAili(AssessmentInventoryLocationItem $aili)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        //$id = $this->getArlcIdByAttrs($arlc->arlc_a_id, $arlc->arlc_adr_id, $arlc->arlc_ar_id);
        //$id ? $data['arlc_id'] = $id : 0;

        $data = array();

        if ($aili->aili_a_id) {
            $data['aili_a_id'] = $aili->aili_a_id;
        }
        if ($aili->aili_ai_id) {
            $data['aili_ai_id'] = $aili->aili_ai_id;
        }
        $data['aili_adr_id'] = $aili->aili_adr_id;
        $data['aili_name'] = $aili->aili_name;
        $data['aili_model'] = $aili->aili_model;
        $data['aili_description'] = $aili->aili_description;

        $id = (int) $aili->aili_id;

        if (!$id) {
            $data['aili_create_u_id'] = $identity['u_id'];
        } else {
            $data['aili_update_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getAili($id)) {
                $data['aili_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('aili_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteAili($id)
    {
        $data['aili_id'] = $id;
        $data['aili_active'] = 0;
        $this->tableGateway->update($data, array('aili_id' => $id));

        return true;
    }
}