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

class AssessmentInventoryLocationReportTable implements ServiceLocatorAwareInterface
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

    public function getAilr($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_id = ' . $id);
        $select->where('ailr_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAilrsByAssessment($aId, $adrId = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_a_id = ' . (int) $aId);
        $select->where('ailr_active = 1');

        if ($adrId) {
            $select->where('ailr_adr_id = ' . (int) $adrId);
        }
        //$select->join(array('f' => 'files'), 'ailr_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }


    public function getAilrByLocation($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_a_id = ' . (int) $aId);
        $select->where('ailr_adr_id = ' . (int) $adrId);
        $select->where('ailr_active = 1');

        $select->join(array('f' => 'files'), 'ailr_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->ailr_ai_id][] = $rs;
        }

        return $ret;
    }

    public function getReportsFiles($aId, $adrId, $aiId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_a_id = ' . (int) $aId);
        $select->where('ailr_adr_id = ' . (int) $adrId);
        $select->where('ailr_ai_id = ' . (int) $aiId);
        $select->where('ailr_active = 1');

        $select->join(array('f' => 'files'), 'ailr_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAilrByLocationAndAi($aId, $adrId, $aiId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_a_id = ' . (int) $aId);
        $select->where('ailr_adr_id = ' . (int) $adrId);
        $select->where('ailr_ai_id = ' . (int) $aiId);
        $select->where('ailr_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAilr($aId, $adrId, $files)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $filesTable = $this->getServiceLocator()->get('Application\Model\FilesTable');

        if (isset($files['report'])) {
            foreach ($files['report'] as $aiId => $filesItems) {
                foreach ($filesItems as $fileCounter => $file) {
                    if (!$file['size']) continue;
                    $reportsFolder = 'public/data/reports';
                    if (!is_dir($reportsFolder)) {
                        mkdir($reportsFolder);
                    }

                    if (!is_dir($reportsFolder . '/' . $aId)) {
                        mkdir($reportsFolder . '/' . $aId);
                    }

                    $tempFile = $file['tmp_name'];
                    $dataFile['f_name'] = $file['name'];
                    $dataFile['f_type'] = $file['type'];

                    $fId = $filesTable->saveFile($dataFile);
                    $ret = move_uploaded_file($tempFile, $reportsFolder . '/' . $aId . '/' . $fId);

                    /*if ($ailr = $this->getAilrByLocationAndAi($aId, $adrId, $aiId)) {
                        $id = $ailr->ailr_id;
                        $dataAilr['ailr_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                        $dataAilr['ailr_update_u_id'] = $identity['u_id'];
                        $dataAilr['ailr_f_id'] = $fId;
                        $this->tableGateway->update($dataAilr, array('ailr_id' => $ailr->ailr_id));
                    } else {*/
                    $dataAilr['ailr_create_u_id'] = $identity['u_id'];
                    $dataAilr['ailr_a_id'] = $aId;
                    $dataAilr['ailr_adr_id'] = $adrId;
                    $dataAilr['ailr_ai_id'] = $aiId;
                    $dataAilr['ailr_f_id'] = $fId;

                    $this->tableGateway->insert($dataAilr);
                    $id = $this->tableGateway->lastInsertValue;
                }
            }
        }

        return true;
    }

    public function saveAilrWithoutFiles($rf)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $dataAilr['ailr_create_u_id'] = $identity['u_id'];
        $dataAilr['ailr_a_id'] = $rf->ailr_a_id;
        $dataAilr['ailr_adr_id'] = $rf->ailr_adr_id;
        $dataAilr['ailr_ai_id'] = $rf->ailr_ai_id;
        $dataAilr['ailr_f_id'] = $rf->ailr_f_id;

        $this->tableGateway->insert($dataAilr);
        $id = $this->tableGateway->lastInsertValue;

        return true;
    }

    public function deleteAilr($id)
    {
        $data['ailr_id'] = $id;
        $data['ailr_active'] = 0;
        $this->tableGateway->update($data, array('ailr_id' => $id));

        return true;
    }

    public function getFileByFId($fId = 0)
    {
        $fId  = (int) $fId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('ailr_f_id = ' . $fId);
        $select->order('ailr_id ASC');
        $select->limit(1);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }


        return $row->ailr_a_id;
    }
}