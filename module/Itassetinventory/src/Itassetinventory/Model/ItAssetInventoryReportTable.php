<?php
namespace Itassetinventory\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ItAssetInventoryReportTable implements ServiceLocatorAwareInterface
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

    public function getIair($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('iair_id = ' . $id);
        $select->where('iair_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getByInventory($iaiId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iair_iai_id = ' . (int) $iaiId);
        $select->where('iair_active = 1');

        $select->join(array('f' => 'files'), 'iair_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->iair_iaiit_id][] = $rs;
        }

        return $ret;
    }

    public function getReportsFiles($iaiId, $iaiitId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iair_iai_id = ' . (int) $iaiId);
        $select->where('iair_iait_id = ' . (int) $iaiitId);
        $select->where('iair_active = 1');

        $select->join(array('f' => 'files'), 'iair_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getIairByIaiAndIait($iaiId, $iaiitId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iair_iai_id = ' . (int) $iaiId);
        $select->where('iair_iait_id = ' . (int) $iaiitId);
        $select->where('iair_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveIair($iaiId, $iaiitId, $files)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $filesTable = $this->getServiceLocator()->get('Application\Model\FilesTable');

        if (isset($files)) {
            foreach ($files as $file) {
                    if (!$file['size']) continue;
                    $reportsFolder = 'public/data/it_asset';
                    if (!is_dir($reportsFolder)) {
                        mkdir($reportsFolder, 0755, true);
                    }

                    if (!is_dir($reportsFolder . '/' . $iaiId)) {
                        mkdir($reportsFolder . '/' . $iaiId);
                    }

                    $tempFile = $file['tmp_name'];
                    $dataFile['f_name'] = $file['name'];
                    $dataFile['f_type'] = $file['type'];

                    $fId = $filesTable->saveFile($dataFile);
                    $ret = move_uploaded_file($tempFile, $reportsFolder . '/' . $iaiId . '/' . $fId);

                    $dataAilr['iair_create_u_id'] = $identity['u_id'];
                    $dataAilr['iair_iai_id'] = $iaiId;
                    $dataAilr['iair_iaiit_id'] = $iaiitId;
                    $dataAilr['iair_f_id'] = $fId;

                    $this->tableGateway->insert($dataAilr);
                    $id = $this->tableGateway->lastInsertValue;
                }
        }

        return true;
    }

    public function deleteIair($id)
    {
        $data['iair_active'] = 0;
        $this->tableGateway->update($data, array('iair_id' => $id));

        return true;
    }

    public function deleteIairByInventory($id)
    {
        $data['iair_active'] = 0;
        $this->tableGateway->update($data, array('iair_iai_id' => $id));

        return true;
    }

    public function unarchiveIairByInventory($id)
    {
        $data['iair_active'] = 1;
        $this->tableGateway->update($data, array('iair_iai_id' => $id));

        return true;
    }

    public function getFileByFId($fId = 0)
    {
        $fId  = (int) $fId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('iair_f_id = ' . $fId);
        $select->order('iair_id ASC');
        $select->limit(1);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }


        return $row->iair_a_id;
    }
}