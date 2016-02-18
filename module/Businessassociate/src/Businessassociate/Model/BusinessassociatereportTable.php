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

        $barDataFile = array();
        $barDataFile['bar_ba_id'] = $ba_id;
        $barDataFile['bar_f_id'] = $f_id_dest;
        $barDataFile['bar_create_u_id'] = $ailr->ailr_create_u_id;
        $barDataFile['bar_create_date'] = $ailr->ailr_create_date;

        $this->tableGateway->insert($barDataFile);
        $bar_id = $this->tableGateway->lastInsertValue;

        if (!$bar_id) return false;

        if (!copy($reportsFolder . '/' . $ailr->ailr_a_id . '/' . $ailr->ailr_f_id, $ba_reportsFolder . '/' . $ba_id . '/' . $f_id_dest)) {
            return false;
        }

        return $bar_id;
    }
}