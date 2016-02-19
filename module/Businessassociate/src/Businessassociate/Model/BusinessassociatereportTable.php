<?php
namespace Businessassociate\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Predicate\PredicateSet;

class BusinessassociatereportTable implements ServiceLocatorAwareInterface
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

    public function getReportsFiles($ba_id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('bar_ba_id = ' . (int) $ba_id);

        $select->join(array('f' => 'files'), 'bar_f_id = f_id', array('_filename' => 'f_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function createReportFromAssessmentInventoryLocationReport($ba_id, \Assessment\Model\AssessmentInventoryLocationReport $ailr)
    {
        $reportsFolder = 'public/data/reports';
        $ba_reportsFolder = 'public/data/ba_reports';

        if (!is_dir($ba_reportsFolder)) {
            mkdir($ba_reportsFolder);
        }

        if (!is_dir($ba_reportsFolder . '/' . $ba_id)) {
            mkdir($ba_reportsFolder . '/' . $ba_id);
        }

        $filesTable = $this->getServiceLocator()->get('Application\Model\FilesTable');

        $file_source = $filesTable->getFile($ailr->ailr_f_id);
        if (!$file_source) return false;

        $dataFile = array();

        $dataFile['f_name']      = $file_source->f_name;
        $dataFile['f_type']      = $file_source->f_type;
        $dataFile['f_encrypted'] = $file_source->f_encrypted;
        $dataFile['f_create_date'] = $file_source->f_create_date;

        $f_id_dest = $filesTable->saveFile($dataFile);
        if (!$f_id_dest) return false;

        if (!copy($reportsFolder . '/' . $ailr->ailr_a_id . '/' . $ailr->ailr_f_id, $ba_reportsFolder . '/' . $ba_id . '/' . $f_id_dest)) {
            return false;
        }

        $barDataFile = array();
        $barDataFile['bar_ba_id'] = $ba_id;
        $barDataFile['bar_f_id'] = $f_id_dest;
        $barDataFile['bar_create_u_id'] = $ailr->ailr_create_u_id;
        $barDataFile['bar_create_date'] = $ailr->ailr_create_date;

        $this->tableGateway->insert($barDataFile);
        $bar_id = $this->tableGateway->lastInsertValue;

        if (!$bar_id) return false;        

        return $bar_id;
    }

    public function saveReports($ba_id, $files)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $ba_reportsFolder = 'public/data/ba_reports';

        if (!is_dir($ba_reportsFolder)) {
            mkdir($ba_reportsFolder);
        }

        if (!is_dir($ba_reportsFolder . '/' . $ba_id)) {
            mkdir($ba_reportsFolder . '/' . $ba_id);
        }

        $filesTable = $this->getServiceLocator()->get('Application\Model\FilesTable');

        if (isset($files['report'])) {
            foreach ($files['report'] as $file) {
                if ($file['tmp_name'] == '') continue;
                if (!$file['size']) continue;

                $tempFile = $file['tmp_name'];
                $dataFile['f_name'] = $file['name'];
                $dataFile['f_type'] = $file['type'];

                $fId = $filesTable->saveFile($dataFile);
                if (!$fId) return false;

                if (!move_uploaded_file($tempFile, $ba_reportsFolder . '/' . $ba_id . '/' . $fId)) return false;

                $barDataFile = array();
                $barDataFile['bar_ba_id'] = $ba_id;
                $barDataFile['bar_f_id'] = $fId;
                $barDataFile['bar_create_u_id'] = $identity['u_id'];

                $this->tableGateway->insert($barDataFile); 
                $bar_id = $this->tableGateway->lastInsertValue; 
                if (!$bar_id) return false;
            }
        }

        return true;
    }
}