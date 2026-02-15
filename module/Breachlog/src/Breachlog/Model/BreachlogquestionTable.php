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
        $questionsAnwers = array();
        foreach ($questions as $question) {
            $questionsAnwers[$question->blq_id] = $question->_bla_value;
        }

        // Determine if either
        // a.) Incident includes identifiable data
        // b.) Incident does not include identifiable data, but de-identified data
        //     can be re-identified
        $isDataIdentifiable =
               ($questionsAnwers[2] == 2 && empty($questionsAnwers[3]))
            || ($questionsAnwers[2] == 1 && $questionsAnwers[3] == 2);

        // Determines if security incident is a breach (i.e., reportable)
        $isIncidentReportable =
               ($questionsAnwers[1] == 2)
            && ($isDataIdentifiable)
            && ($questionsAnwers[4] == 2)
            && ($questionsAnwers[5] == 1)
            && ($questionsAnwers[6] == 1)
            && ($questionsAnwers[7] == 2)
            && ($questionsAnwers[8] == 1)
            && ($questionsAnwers[11] == 1);

        if ($isIncidentReportable) {

            $blDb = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable');

            $bl = $blDb->getBreachlog($blId);

            $bl->bl_reportable = 1;
            $bl->breach_locked = 1;
            
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
            $dataBrp['brp_incident_date']     = $bl->bl_date_of_occurrence;
            
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

                // A media release is not necessary when 500 or fewer
                // individuals are compromised.
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

                // **NOTE** This is logically equivalent to the previous code, but less confusing
                // This piece of code determines resolution dates for various remediation plan
                // actions, including required HHS reporting.

                // Set default date for most tasks, 60 days from now
                $brpaData['brpa_target_date'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60);

                // This task specifically relates to HHS reporting.
                // If 500 or less individuals are affected, reporting
                //is required within 60 days of end of calendar year.
                if ($taskKey == 10 && $blSize <= 500) {
                    // If 500 or less individuals are affected, reporting is required within 60 days
                    // of end of calendar year.
                    $date = date('Y-12-31', time());
                    $date = date('Y-m-d H:i:s', strtotime('+59 days', strtotime($date)));
                    $brpaData['brpa_target_date'] = $date;
                }

                // Since these tasks require feedback from HHS, they have an indeterminate date
                if (in_array($taskKey, array(11, 12))) {
                    $brpaData['brpa_target_date'] = '';
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