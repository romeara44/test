<?php
namespace Sitesetting\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Zend\Db\Sql\Expression;

class SitesettingTable implements ServiceLocatorAwareInterface
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

    public function getSitesettings()
    {
        return $this->tableGateway->select();
    }

    public function getSitesetting($id)
    {        
        $rowset = $this->tableGateway->select(array('ss_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveSitesetting(Sitesetting $Sitesetting)
    {

        $data = array(
            'ss_value' => $Sitesetting->ss_value 
        );

        $id = (int) $Sitesetting->ss_id;

        if ($id != 0) {
            if ($this->getSitesetting($id)) {
                $this->tableGateway->update($data, array('ss_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_ST, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function getValueByName($name)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('ss_name = "'. $name . '"');

        $resultSet = $this->tableGateway->selectWith($select)->current();

        return $resultSet ? $resultSet->ss_value : false;
    }


}