<?php
namespace Breachlog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BreachloganswerTable implements ServiceLocatorAwareInterface
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

    public function getBreachloganswers($uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('blq_active = 1');

        $select->order('blq_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getBreachlogAnswerId($breachlogId, $breachlogQuestionAsnwerId){
        $select = $this->tableGateway->getSql()->select();
        $select->where('bla_bl_id = ' . $breachlogId);
        $select->where('bla_blq_id = ' . $breachlogQuestionAsnwerId);

        $resultSet = $this->tableGateway->selectWith($select);
        $row = $resultSet->current();

        return $row;

    } 

    public function getBreachlogAnswer($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('bla_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function saveBreachloganswers(Breachloganswer $bla)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'bla_blq_id' => $bla->bla_blq_id,
            'bla_bl_id' => $bla->bla_bl_id,
            'bla_u_id' => $identity['u_id'],
            'bla_value' => $bla->bla_value,
        );

        $id = (int) $bla->bla_id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getBreachlogAnswer($id)) {
                $this->tableGateway->update($data, array('bla_id' => $id));
            } else {
                throw new \Exception('Question id does not exist');
            }
        }

        return $id;
    }

    public function deleteBreachlogAnswerById($blaId)
    {
        $this->tableGateway->delete("bla_id = $blaId");
    }
    

}