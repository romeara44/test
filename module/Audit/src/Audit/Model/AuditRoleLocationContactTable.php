<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Audit\Model\AuditRoleLocationContact;

class AuditRoleLocationContactTable implements ServiceLocatorAwareInterface
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

    public function getAuditRoleLocationContact($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_role_location_contact_id = ' . $id);
        $select->where('active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAuditRoleLocationContactByLocation($auditRecordId, $addressId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_id = ' . (int) $auditRecordId);
        $select->where('address_id = ' . (int) $addressId);
        $select->where('active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAuditRoleLocationContactByAttrs($auditRecordId, $addressId, $hipaaSuiteModuleRoleId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_id = ' . (int) $auditRecordId);
        $select->where('address_id = ' . (int) $addressId);
        $select->where('hipaa_suite_module_role_id = ' . (int) $hipaaSuiteModuleRoleId);
        $select->where('active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return 0;
        }

        return $row->audit_role_location_contact_id;
    }

    public function saveAuditRoleLocationContact(AuditRoleLocationContact $auditRoleLocationContact)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id = $this->getAuditRoleLocationContactByAttrs($auditRoleLocationContact->audit_record_id, $auditRoleLocationContact->address_id, $auditRoleLocationContact->hipaa_suite_module_role_id);
        $id ? $data['audit_role_location_contact_id'] = $id : 0;

        $data = array();

        $data['audit_record_id'] = $auditRoleLocationContact->audit_record_id;
        $data['address_id'] = $auditRoleLocationContact->address_id;
        $data['hipaa_suite_module_role_id'] = $auditRoleLocationContact->hipaa_suite_module_role_id;
        $data['user_id'] = $auditRoleLocationContact->user_id;
        $data['active'] = $auditRoleLocationContact->active;

        if (!$id) {
            $data['create_user_id'] = $identity['u_id'];
        } else {
            $data['update_user_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

        } else {
            if ($this->getAuditRoleLocationContact($id)) {
                //$data['arlc_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('audit_role_location_contact_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }


    public function saveAuditContacts($leadAuditorId, $auditRoleLocationContacts, $locationId, $updateAuditRecordId) {
        $leadAuditor = 0;
        //$auditRoleLocationContacts = $this->params()->fromPost('auditRoleLocationContact');
        foreach ($auditRoleLocationContacts as $key => $r)
        {
            $test = $auditRoleLocationContacts[$key];
            if ($auditRoleLocationContacts[$key] != ''){
                $auditRoleLocationContact = new AuditRoleLocationContact();
                $auditRoleLocationContact->audit_record_id = $updateAuditRecordId;

                $auditRoleLocationContact->address_id = $locationId;
                $auditRoleLocationContact->hipaa_suite_module_role_id = $key;
                $auditRoleLocationContact->user_id =  $auditRoleLocationContacts[$key];
                $auditRoleLocationContact->active = 1;

                $auditRoleLocationContactId = $this->getServiceLocator()->get('Audit\Model\AuditRoleLocationContactTable')->saveAuditRoleLocationContact($auditRoleLocationContact);

                $hipaaSuiteModuleRoleResult = $this->getServiceLocator()->get('Client\Model\HipaaSuiteModuleRoleTable')->getHipaaSuiteModuleRoleId($key);
                if ($hipaaSuiteModuleRoleResult->company_master_role_id == $leadAuditorId){
                    $leadAuditor = $auditRoleLocationContacts[$key];
                }
            }
            
        }

        return $leadAuditor;
    }
}