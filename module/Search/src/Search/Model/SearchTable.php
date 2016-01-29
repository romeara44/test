<?php
namespace Search\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;

class SearchTable implements ServiceLocatorAwareInterface
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

    public function getResults($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = 0)
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        if ($roleFilter != 'a') {
            switch ($roleFilter) {
                case 'company':
                    $select = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
                case 'contact':
                    $select = $this->getServiceLocator()->get('Admin\Model\UserTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
                case 'breachlog':
                    $select = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
                case 'assessment':
                    $select = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
                case 'remediationplan':
                    $select = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
                case 'user':
                    $select = $this->getServiceLocator()->get('Admin\Model\UserTable')->getSearchResultsUsersSelect($searchValue, $identity);
                    break;
                default:
                    $select = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getSearchResultsSelect($searchValue, $identity);
                    break;
            }
        } else {
            $companiesSelect = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getSearchResultsSelect($searchValue, $identity);
            $contactsSelect = $this->getServiceLocator()->get('Admin\Model\UserTable')->getSearchResultsSelect($searchValue, $identity);
            $usersSelect = $this->getServiceLocator()->get('Admin\Model\UserTable')->getSearchResultsUsersSelect($searchValue, $identity);
            $breachlogSelect = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getSearchResultsSelect($searchValue, $identity);
            $assessmentsSelect = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getSearchResultsSelect($searchValue, $identity);
            $remediationPlanSelect = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getSearchResultsSelect($searchValue, $identity);

            $sql = new Sql($adapter);

            $contactsSelect->combine($breachlogSelect, 'union', 'all');

            $select = $sql->select();
            $select->from(array('sel1and2' => $contactsSelect));
            $select->combine($companiesSelect, 'union', 'all');

            $select2 = $sql->select();
            $select2->from(array('sel2and3' => $select));
            $select2->combine($assessmentsSelect, 'union', 'all');

            $select3 = $sql->select();
            $select3->from(array('sel3and4' => $select2));
            $select3->combine($remediationPlanSelect, 'union', 'all');

            $select4 = $sql->select();
            $select4->from(array('sel4and5' => $select3));
            $select4->combine($usersSelect, 'union', 'all');

            $select = $select4;
        }

        if ($orderBy) {
            $order = $order ? $order : 'ASC';
            $select->order($orderBy . ' ' . $order);
        }

        $resultSetPrototype = new ResultSet();
        $paginatorAdapter = new DbSelect(
            $select,
            $adapter,
            $resultSetPrototype
        );


        //echo $select2->getSqlString($this->tableGateway->getAdapter()->getPlatform());
//        die;

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }


}