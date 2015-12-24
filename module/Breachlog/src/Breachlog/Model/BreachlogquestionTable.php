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
use Breachlog\Model\Breachremediationplan;
use Breachlog\Model\Breachremediationplanaction;

class BreachlogquestionTable implements ServiceLocatorAwareInterface
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

    public function getBreachlogquestions()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('blq_active = 1');

        $select->order('blq_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getBreachlogquestionsWithAnswers($blId = 0)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->join(array('bla' => 'breach_logs_answers'), new \Zend\Db\Sql\Expression('bla_blq_id = blq_id AND bla_bl_id = ' . $blId), array('_bla_value' => 'bla_value'), 'left');

        $select->where('blq_active = 1');

        $select->order('blq_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function setReportable($blId = 0)
    {
        $questions = $this->getBreachlogquestionsWithAnswers($blId);

       // echo '<pre>';

        $questionsAnwers = array();

        foreach ($questions as $question) {
            $questionsAnwers[$question->blq_id] = $question->_bla_value;
        }
        if (($questionsAnwers[1] == 2) && ($questionsAnwers[2] == 2) && (empty($questionsAnwers[3]) || $questionsAnwers[3] == 2) 
            && ($questionsAnwers[4] == 2) && ($questionsAnwers[5] == 1) && ($questionsAnwers[6] == 1) && ($questionsAnwers[7] == 2) 
            && ($questionsAnwers[8] == 1) && ($questionsAnwers[11] == 1)) {

            $blDb = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable');

            $bl = $blDb->getBreachlog($blId);

            $bl->bl_reportable = 1;

            $blDb->saveBreachlog($bl);

            $authService = new \Zend\Authentication\AuthenticationService();
            $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
            $identity = $authService->getIdentity();

            $dataBrp = array();

            $dataBrp['brp_bl_id']             = $blId;
            $dataBrp['brp_c_id']              = $bl->bl_c_id;
            $dataBrp['brp_consultant_u_id']   = $bl->bl_consultant_u_id;
            $dataBrp['brp_accepter_u_id']     = $bl->bl_accepter_u_id;
            $dataBrp['brp_approver_u_id']     = $bl->bl_approver_u_id;
            $dataBrp['brp_initials_approver'] = $bl->bl_initials_approver;
            $dataBrp['brp_initials']          = $bl->bl_initials;
            $dataBrp['brp_performed_u_id']    = $identity['u_id'];
            $dataBrp['brp_remediation_date']  = date('Y-m-d H:i:s');
            $dataBrp['brp_incident_date']     = date('Y-m-d H:i:s');

            $brpDb = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable');

            $brp = new Breachremediationplan();

            $brp->exchangeArray($dataBrp);

            $brpDb->setServiceLocator($this->getServiceLocator());

            $brpId = $brpDb->saveBreachremediationplan($brp);

            $brpDb->setRegulations($brpId, $bl->_bl_cur_regulations);

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_BRP, $brpId);

            $blSize = (int) $bl->bl_size;

            $approval_authority_role = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getCompanyRoleByCompanyAndRole($bl->bl_c_id, 9);

            foreach (Breachremediationplanaction::$tasks as $taskKey => $task) {

                if (($taskKey == 9) && ($blSize <= 500)) {
                    continue;
                }

                $brpa = new Breachremediationplanaction();

                $brpaData['brpa_brp_id']       = $brpId;
                $brpaData['brpa_contact_u_id'] = $bl->bl_consultant_u_id;
                $brpaData['brpa_approver_u_id'] = $approval_authority_role->cr_u_id;
                $brpaData['brpa_task']         = $task;
                $brpaData['brpa_action_plan']  = '';
                $brpaData['brpa_status']       = 0;

                if (in_array($taskKey, array(9, 10, 11, 12))) {
                    if (in_array($taskKey, array(11, 12))) {
                        $brpaData['brpa_target_date'] = '';
                    } elseif (in_array($taskKey, array(9))) {
                        $brpaData['brpa_target_date'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60);
                    } else {
                        if ($blSize > 500) {
                            $brpaData['brpa_target_date'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60);
                        } else {
                            $brpaData['brpa_target_date'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60);
                        }
                    }
                } else {
                    $brpaData['brpa_target_date'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60);
                }

                $brpa->exchangeArray($brpaData);

                $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanactionTable')->setServiceLocator($this->getServiceLocator());
                $brpaId = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanactionTable')->saveBreachremediationplanaction($brpa);
            }

            return $brpId;
        }

        return 0;
    }

}