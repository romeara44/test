<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;



class AuditRecordTable implements ServiceLocatorAwareInterface
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

    public function getOpenAuditRecords($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('audit_record');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new AuditRecord());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            $paginator = new Paginator($paginatorAdapter);
            
            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_status = 0');

        $resultSet = $this->tableGateway->selectWith($select);
                        
        return $resultSet;

    }

    public function getAuditRecords($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        //if ($paginated) {
            $select = new Select('audit_record');
            //$resultSetPrototype = new ResultSet();
            //$resultSetPrototype->setArrayObjectPrototype(new AuditRecord());
            // $paginatorAdapter = new DbSelect(
            //     $select,
            //     $this->tableGateway->getAdapter(),
            //     $resultSetPrototype
            // );

            $select->join(array('s' => 'lu_status'), 'audit_status_id = s.status_id', array('_audit_status_name' => 'display_description'));
            $select->join(array('u' => 'users'), 'auditor_u_id = u.u_id', array('_user_first_name' => 'u_firstname', '_user_last_name' => 'u_lastname'));

            $order = $order ? $order : 'ASC';

            if ($orderBy) {
                $orders[] = $orderBy . ' ' . $order;
            }

            $select->order($orders);

            //$paginator = new Paginator($paginatorAdapter);
            
            //return $paginator;
        //}

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;

    }
    

    public function getAuditRecord($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_id = ' . $id);
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAuditRecord(AuditRecord $audit_record)
    {
        $id = (int)$audit_record->audit_record_id;

        $data = array();

        $data['rp_id'] = $audit_record->rp_id;
        $data['audit_status_id'] = $audit_record->audit_status_id;
        $data['company_id'] = $audit_record->company_id;
        $data['auditor_u_id'] = $audit_record->auditor_u_id;
        

        if ($id == 0)
        {
            $data['date_audited'] = $audit_record->date_audited;
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }
        else 
        {
            if ($this->getAuditRecord($id))
            {
                
                $data['auditor_u_id'] = $audit_record->auditor_u_id;
                //$data['date_audited'] = $audit_record->date_audited;
                $data['reviewed_u_id'] = empty($audit_record->reviewed_u_id) ? null : $audit_record->reviewed_u_id;
                $data['reviewed_initials'] = empty($audit_record->reviewed_initials) ? null : $audit_record->reviewed_initials;
                $data['reviewed_date'] = empty($audit_record->reviewed_date) ? null : $audit_record->reviewed_date;
                $data['auditor_approve_u_id'] = empty($audit_record->auditor_approve_u_id) ? null : $audit_record->auditor_approve_u_id;
                $data['auditor_approve_initials'] = empty($audit_record->auditor_approve_initials) ? null : $audit_record->auditor_approve_initials;
                $data['auditor_approve_date'] = empty($audit_record->auditor_approve_date) ? null : $audit_record->auditor_approve_date;
                $data['is_locked'] = $audit_record->is_locked;

                $this->tableGateway->update($data, array('audit_record_id' => $id));
            }
            else 
            {
                throw new \Exception('Audit Record Id does not exist');
            }
            
        }

        return $id;
    }

    public function getReviewApprove($id)
    {
        $id     = (int) $id;
        $result = array();

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array(new \Zend\Db\Sql\Expression('DISTINCT(u1.u_id) as u_id')));
        $select->where('rp_id = ' . $id);
        $select->join(array('arlc' => 'assessments_roles_locations_contacts'), 'arlc.arlc_a_id = rp_a_id and arlc.arlc_adr_id = rp_adr_id', array(), 'inner');
        $select->join(array('ar' => 'assessments_roles'), 'arlc.arlc_ar_id = ar.ar_id', array('_ar_id' => 'ar_id'), 'inner');
        $select->join(array('u1' => 'users'), 'arlc.arlc_u_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');
        $select->where('ar.ar_id IN(8)');
        
        $resultSet = $this->tableGateway->selectWith($select);

        if(!$resultSet->count()) {
            $select = $this->tableGateway->getSql()->select();
            $select->where('rp_id = ' . $id);
            $select->join(array('cr' => 'company_roles'), 'cr.cr_c_id = rp_c_id', array('_ar_id' => 'cr_ar_id'), 'inner');
            $select->join(array('u1' => 'users'), 'cr.cr_u_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');
            $select->where('cr.cr_ar_id IN(8)');

            $resultSet = $this->tableGateway->selectWith($select);
        }
        
        if($resultSet->count()) {
            foreach ($resultSet as $key => $rs) {
                if(!isset($result[$rs->_u_id])) {
                    $result[$rs->_u_id] = $rs;
                    $result[$rs->_u_id]->_ar_id = array($rs->_ar_id);
                } else {
                    $result[$rs->_u_id]->_ar_id[] = $rs->_ar_id;
                }
            }
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_id = ' . $id);
        $select->join(array('c' => 'companies'), 'c.c_id = rp_c_id', array(), 'inner');
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c.c_id', array(), 'inner');
        $select->join(array('u1' => 'users'), 'cc.cc_consultant_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');

        $resultSet = $this->tableGateway->selectWith($select);

        if($resultSet) {
            foreach ($resultSet as $rs) {
                if(!isset($result[$rs->_u_id])) {
                    $result[$rs->_u_id] = $rs;
                    $result[$rs->_u_id]->_ar_id = array();
                }
            }
        }
        
        return $result;
    }



    public function getUnansweredItemsCount($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select(); //FROM audit_record ar
        $select->join(array('art' => 'audit_record_type'), 'art.audit_record_id = audit_record.audit_record_id', array(), 'inner');
        $select->join(array('ars' => 'audit_record_section'), 'ars.audit_record_type_id = art.audit_record_type_id', array(), 'inner');
        $select->join(array('ari' => 'audit_record_item'), 'ari.audit_record_section_id = ars.audit_record_section_id', array(), 'inner');
        $select->where('audit_record.audit_record_id = '. $id);
        $select->where('ari.item_status_id = 4');

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet->count();
    }

    public function getAuditorApproveSignoff($id)
    {
        
        $id     = (int) $id;
        $result = array();

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array(new \Zend\Db\Sql\Expression('DISTINCT(u1.u_id) as u_id')));
        $select->join(array('arlc' => 'audit_role_location_contact'), 'arlc.audit_record_id = audit_record.audit_record_id', array(), 'inner');
        $select->join(array('u1' => 'users'), 'arlc.user_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');
        $select->where('audit_record.company_id = ' . $id);
        $select->where('arlc.hipaa_suite_module_role_id IN(1,2,3)');
        
        $resultSet = $this->tableGateway->selectWith($select);

        if($resultSet->count()) {
            foreach ($resultSet as $key => $rs) {
                if(!isset($result[$rs->_u_id])) {
                    $test = $rs->_u_name;
                    $result1[$rs->_u_id] = array("u_id" => $rs->_u_id, "u_name" => $rs->_u_name);

                }
            }
        }
        
        return $result1;
    }

    public function cloneAuditRecord(AuditRecord $auditRecord) {
        // Audit Record Copy
        $newAuditRecord = new AuditRecord();

        $newAuditRecord->rp_id = $auditRecord->rp_id;
        $newAuditRecord->company_id = $auditRecord->company_id;
        $newAuditRecord->auditor_u_id = $auditRecord->auditor_u_id;
        $newAuditRecord->audit_status_id = \Audit\Model\AuditRecord::AUDIT_STATUS;//5;
        $newAuditRecord->is_locked = 0;

        $savedAuditRecordId = $this->saveAuditRecord($newAuditRecord);

        return $savedAuditRecordId;

    }

}