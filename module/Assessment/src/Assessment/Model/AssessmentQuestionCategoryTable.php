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

class AssessmentQuestionCategoryTable implements ServiceLocatorAwareInterface
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

    public function getCategories()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqc_active = 1');
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function getCategoriesByType($type = 1, $additional_location = 0, $company = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aq_type = ' . $type);
        $select->where('aq_active = 1');

        if ($company && $type == \Assessment\Model\Assessment::TYPE_SECURITY_RISK) {
            if ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD && $company->c_type == \Client\Model\Company::CHILD_TYPE_LOCATION_ONLY) {
                $additional_location = 1;
            }
        }

        if ($additional_location) {
            $select->where('aqc_additional_location = 1');
        }

        $select->join(array('aq' => 'assessments_questions'), 'aq_aqc_id = aqc_id', array('*'));
        $select->group('aqc_id');
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }
}