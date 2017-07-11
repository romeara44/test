<?php
namespace Traininglog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class EmployeemasterlistTable implements ServiceLocatorAwareInterface
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

    public function getEmployeemasterlists()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $companies = $this->serviceLocator->get('Client\Model\CompanyTable')->getCompaniesPairs();
            if ($companies) {
                $select->where('eml_company_id IN (' . implode(',', array_keys($companies)) . ')');
            } else {
                $select->where('1 != 1');
            }
        }
        
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getEmployeemasterlist($company_id)
    {
        $res = array();

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('eml_list'));
        $select->where('eml_company_id = ' . $company_id);
        
        $resultSet = $this->tableGateway->selectWith($select);
        $row = $resultSet->current();
        if (!$row) {
            return $res;
        }
        
        $items = explode("\n", $row->eml_list);
        if ($items) {
            foreach ($items as $item) {
                $res[$item] = $item;
            }
        }

        return $res;
    }

    public function saveEmployeemasterlists($post)
    {
        $db           = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $sql          = new Sql( $db );
        $delete       = $sql->delete('employee_master_list');
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        
        $db->query($deleteString, $db::QUERY_MODE_EXECUTE);

        if (isset($post['eml_company_id'])) {
            
            foreach ($post['eml_company_id'] as $key => $val) {
                $company_id = (int)$post['eml_company_id'][$key];
                $list = trim($post['eml_list'][$key]);
                if (!$company_id || !$list) continue;
                $items = explode("\n", str_replace("\r", "", $list));
                $list = '';
                if ($items) {
                    foreach ($items as $item) {
                        $item = trim($item);
                        if ($item) {
                            $list .= $item . "\n";    
                        }                        
                    }
                }
                $list = rtrim($list, "\n");
                if (!$list) continue;
                $data = array();
                $data['eml_company_id'] = $company_id;
                $data['eml_list'] = $list;
                $this->tableGateway->insert($data);               
            }
        }
    }

}