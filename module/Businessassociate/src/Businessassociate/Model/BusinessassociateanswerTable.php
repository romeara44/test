<?php
namespace Businessassociate\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BusinessassociateanswerTable implements ServiceLocatorAwareInterface
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

    public function getBusinessassociateanswers($uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('baq_active = 1');

        $select->order('baq_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function saveBusinessassociateanswers(Businessassociateanswer $baa)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'baa_baq_id' => $baa->baa_baq_id,
            'baa_u_id' => $identity['u_id'],
            'baa_value' => $baa->baa_value,
            'baa_ba_id' => $baa->baa_ba_id
        );

        $id = (int) $baa->baa_id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }

        return $id;
    }

    public function checkIfUserAnswered($uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('baa_u_id = ' . $uId);
        $select->where('baa_active = 1');

        $select->group('baa_u_id');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        return is_object($row) ? true : false;
    }

    public function checkIfAnswersExists($baId = 0)
    {
        if (is_null($baId)){
            $baId = 0;
        }
        $select = $this->tableGateway->getSql()->select();
        $select->where('baa_ba_id = ' . $baId);
        $select->where('baa_active = 1');

        $select->group('baa_u_id');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        return is_object($row) ? true : false;
    }

    public function getAnswers($uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('baa_u_id = ' . $uId);

        $select->join(array('baq' => 'business_associates_questions'), 'baa_baq_id = baq_id', array('_baq_title' => 'baq_title'), 'inner');
        $select->join(array('n' => 'notes'), new \Zend\Db\Sql\Expression('note_item_id = baa_id AND note_item_type = 3'), array('note_id', '_note_text' => 'note_text'), 'left');
        $select->join(array('nf' => 'notes_files'), 'note_id = nf_note_id', array('*', '_files' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(CONCAT(f_name, "::", f_id))')), 'left');
        $select->join(array('f' => 'files'), 'nf_f_id = f_id', array('*'), 'left');

        $select->where('baq_active = 1');
        $select->order('baq_order ASC');

        $select->group(array('baa_baq_id'));

        //echo str_replace('"', '', $select->getSqlString());
        //die;

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getBaAnswers($baId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('baa_ba_id = ' . $baId);

        $select->join(array('baq' => 'business_associates_questions'), 'baa_baq_id = baq_id', array('_baq_title' => 'baq_title'), 'inner');
        $select->join(array('n' => 'notes'), new \Zend\Db\Sql\Expression('note_item_id = baa_id AND note_item_type = 3'), array('note_id', '_note_text' => 'note_text'), 'left');
        $select->join(array('nf' => 'notes_files'), 'note_id = nf_note_id', array('*', '_files' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(CONCAT(f_name, "::", f_id))')), 'left');
        $select->join(array('f' => 'files'), 'nf_f_id = f_id', array('*'), 'left');

        $select->where('baa_active = 1');
        $select->where('baq_active = 1');
        $select->order('baq_order ASC');

        $select->group(array('baa_baq_id'));

        $resultSet = $this->tableGateway->selectWith($select);

        $answers = array();
        foreach ($resultSet as $rs) {
            $answers[$rs->baa_baq_id] = $rs;
        }

        return $answers;
    }

    public function deleteBusinessassociateanswers($baId)
    {
        $data['baa_active'] = 0;
        $this->tableGateway->update($data, array('baa_ba_id' => $baId));

        return true;
    }

}