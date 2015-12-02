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

class AssessmentQuestionTable implements ServiceLocatorAwareInterface
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

    public function getQuestion($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('aq_id = ' . $id);
        $select->where('aq_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return '';
        }

        return $row->ar_name;
    }

    public function getQuestions($type = \Assessment\Model\Assessment::TYPE_SECURITY_RISK, $aRole = 1, $aId = 0, $location = 0, $aObj = null)
    {
        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($aId, \Client\Model\AddressItem::ASSESSMENT_TYPE);

        $counterAdr = 0;
        $adrId = 0;
        foreach ($addresses->buffer() as $address) {
            if (!$counterAdr) {
                $adrId = $adrIdB = $address->adr_id;
                break;
            }
        }

        $additionalAddress = ($adrId != $location) && $adrId && $location ? true : false;

        if ($aObj) {
            $company = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompany($aObj->a_c_id);
            if ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD && $company->c_type == \Client\Model\Company::CHILD_TYPE_LOCATION_ONLY) {
                $additionalAddress = true;
            }
        }

        //die;
        $select = $this->tableGateway->getSql()->select();
        $select->where('aq_type = ' . $type);
        $select->where('aq_active = 1');

        $select->join(array('aqo' => 'assessments_questions_options'), 'aqo_aq_id = aq_id', array('*', '_options' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(CONCAT(aqo_id, "::", aqo_title) ORDER BY aqo_order)')), 'left');
        $select->join(array('aqc' => 'assessments_questions_categories'), 'aq_aqc_id = aqc_id', array('*'), 'left');

        if ($additionalAddress) {
            $select->where('aqc_additional_location = 1');
        }

        $select->where('aqc_ar_id = ' . (int) $aRole);

        $select->order('aq_order ASC');
        $select->group('aqo_aq_id');

        $resultSet = $this->tableGateway->selectWith($select);
        $resultSet2 = $this->tableGateway->selectWith($select);

        $qCats = array();
        foreach ($resultSet->buffer() as $rs) {
            if (!(int) $rs->aq_parent_aq_id) {
                $obj = (array) $rs;
                foreach ($resultSet2->buffer() as $rs2) {
                    if (($rs2->aq_parent_aq_id == $rs->aq_id) && ($rs2->aq_aqc_id == $rs->aq_aqc_id)) {
                        $obj['children'][] = (array) $rs2;
                    }
                }

                $catDesc['aqc_parent_id'] = $rs->aqc_parent_id;
                $catDesc['aqc_name'] = $rs->aqc_name;
                $catDesc['aqc_citation'] = $rs->aqc_citation;
                $catDesc['aqc_specification'] = $rs->aqc_specification;
                $catDesc['aqc_description'] = $rs->aqc_description;
                $catDesc['aqc_policy'] = $rs->aqc_policy;

                if (!isset($qCats[$rs->aq_aqc_id]['cat'])) {
                    $catDesc['aqc_citation'] = str_replace('Â', '', $catDesc['aqc_citation']);
                    $qCats[$rs->aq_aqc_id]['cat'] = $catDesc;
                }
                $qCats[$rs->aq_aqc_id]['elements'][] = $obj;
            }
        }


        return $qCats;
    }

    public function getQuestionsIdsByCategory($catId = 0)
    {
        $catId = (int) $catId;
        $select = $this->tableGateway->getSql()->select();
        $select->where('aq_active = 1');

        $select->where('aq_aqc_id = ' . $catId);

        $select->order('aq_order ASC');
        $select->group('aq_id');

        $resultSet = $this->tableGateway->selectWith($select);
        $ids = array();
        foreach ($resultSet->buffer() as $rs) {
            $ids[$rs->aq_id] = $rs->aq_id;
        }
        return $ids;
    }

    public function getQuestionsTest()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aq_type = 1');
        $select->where('aq_active = 1');

        $select->where('aq_id > 7');

        $select->order('aq_order ASC');
        $select->group('aq_id');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }
}