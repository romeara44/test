<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Note\Model\Note;

use Assessment\Model\Remediationplan;
use Assessment\Model\Remediationplanaction;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AssessmentQuestionAnswerTable implements ServiceLocatorAwareInterface
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

    public function getAqa($aId, $adrId, $assessmentRole, $questionId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqa_a_id = ' . $aId);
        $select->where('aqa_adr_id = ' . $adrId);
        $select->where('aqa_aq_id = ' . $questionId);
        $select->where('aqa_ar_id = ' . $assessmentRole);

        $select->where('aqa_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function deleteAqa($aId, $adrId, $assessmentRole)
    {
        $this->tableGateway->delete("aqa_a_id = $aId AND aqa_adr_id = $adrId AND aqa_ar_id = $assessmentRole");
    }

    public function deleteNonUsedAqa($aId, $adrId, $assessmentRole, $questionsUsed)
    {
        $this->tableGateway->delete("aqa_a_id = $aId AND aqa_adr_id = $adrId AND aqa_ar_id = $assessmentRole AND aqa_aq_id NOT IN(" . implode(',', $questionsUsed) . ")");
    }

    public function checkStep($aId, $adrId, $arId, $questions)
    {
        /*$select = $this->tableGateway->getSql()->select();
        $select->where('aqa_a_id = ' . (int) $aId);
        $select->where('aqa_adr_id = ' . (int) $adrId);
        $select->where('aqa_ar_id = ' . $arId);
        $select->where('aqa_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }*/

        $answers = $this->getAqas($aId, $adrId, $arId);

        $counterQuestions = 1;
        $counterQuestionsToAnswered = 0;
        foreach ($questions as $question) {
            foreach ($question['elements'] as $questionEl) {
                $_options = $questionEl['_options'];
                $_optionsT = explode(',', $_options);
                $isYes = false;
                foreach ($_optionsT as $_option) {
                    $_optionT = explode('::', $_option);
                    $isYes = isset($answers[$questionEl['aq_id']]) && ($answers[$questionEl['aq_id']]['answerId'] == $_optionT[0]) && ($_optionT[1] == 'Yes') ? true : false;
                    if (isset($answers[$questionEl['aq_id']]) && ($questionEl['aq_id'] == 14)) {
                        $isYes = isset($answers[$questionEl['aq_id']]) && ($answers[$questionEl['aq_id']]['answerId'] == $_optionT[0]) && ($_optionT[1] == 'No') ? true : false;
                    }
                    if ($isYes) {
                        break;
                    }
                }

                $counterQuestionsToAnswered++;

                if ($isYes) {
                    if (isset($questionEl['children'])) {
                        $counterQuestionsToAnswered += count($questionEl['children']);
                    }
                }
            }
        }

        return (count($answers) == $counterQuestionsToAnswered) ? true : false;
    }

    public function getAqasByAssessment($aId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqa_a_id = ' . $aId);
        $select->where('aqa_adr_id = ' . $adrId);

        $select->where('aqa_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAqas($aId, $adrId, $assessmentRole)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqa_a_id = ' . $aId);
        $select->where('aqa_adr_id = ' . $adrId);
        $select->where('aqa_ar_id = ' . $assessmentRole);

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($aId, \Client\Model\AddressItem::ASSESSMENT_TYPE);

        $counterAdr = 0;
        foreach ($addresses->buffer() as $address) {
            if (!$counterAdr) {
                $adrIdFirst = $adrIdB = $address->adr_id;
                break;
            }
        }

        $additionalAddress = ($adrIdFirst != $adrId) ? true : false;
        if ($additionalAddress) {
            $select->join(array('aq' => 'assessments_questions'), 'aq_id = aqa_aq_id', array('*'), 'left');
            $select->join(array('aqc' => 'assessments_questions_categories'), 'aqc_id = aq_aqc_id', array('*'), 'left');
            $select->where('aqc_additional_location = 1');
        }

        $select->where('aqa_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->aqa_aq_id]['answerId'] = $rs->aqa_aqo_id;
            $ret[$rs->aqa_aq_id]['notes'] = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNotes($rs->aqa_id, \Note\Model\Note::NOTE_ASSESSMENT_ANSWER, $adrId);

        }

        return $ret;
    }

    public function getAnswersIdsByQuestion($aId, $adrId, $questionsIds)
    {
        $questionsIds[] = 0;
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqa_a_id = ' . $aId);
        $select->where('aqa_adr_id = ' . $adrId);
        $select->where('aqa_aq_id IN(' . implode(',', $questionsIds). ')');

        $select->where('aqa_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ids = array();
        foreach ($resultSet->buffer() as $rs) {
            $ids[$rs->aqa_id] = $rs->aqa_id;
        }
        return $ids;
    }

    public function getAnswersScore($aId, $aqcId, $adrId)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->join(array('aq' => 'assessments_questions'), 'aq_id = aqa_aq_id', array('aq_id'));
        $select->join(array('aqc' => 'assessments_questions_categories'), 'aq_aqc_id = aqc_id', array('aqc_id'));
        $select->join(array('aqo' => 'assessments_questions_options'), 'aqa_aqo_id = aqo_id', array('_score' =>  new \Zend\Db\Sql\Expression('SUM(aqo_risk_score)')));

        $select->where('aq_aqc_id = ' . $aqcId);
        $select->where('aqa_a_id = ' . $aId);
        $select->where('aqa_adr_id = ' . $adrId);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAqaByObj(AssessmentQuestionAnswer $answer)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $dataAnswer['aqa_a_id'] = $answer->aqa_a_id;
        $dataAnswer['aqa_adr_id'] = $answer->aqa_adr_id;
        $dataAnswer['aqa_aq_id'] = $answer->aqa_aq_id;
        $dataAnswer['aqa_ar_id'] = $answer->aqa_ar_id;
        $dataAnswer['aqa_aqo_id'] = $answer->aqa_aqo_id;

        $dataAnswer['aqa_create_date'] = new \Zend\Db\Sql\Expression('NOW()');
        $dataAnswer['aqa_create_u_id'] = $identity['u_id'];

        $aqaId = $this->tableGateway->insert($dataAnswer);
        $aqaId = $this->tableGateway->lastInsertValue;

        return $aqaId;
    }

    public function saveAqa($aId, $adrId, $assessmentRole, $post, $files)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (isset($post['question'])) {

            $questionsUsed = array(0);
            foreach ($post['question'] as $questionId => $answerId) {
                $dataAnswer = array();
                $questionsUsed[] = $questionId;
                $dataAnswer['aqa_aqo_id'] = $answerId;

                if ($aqa = $this->getAqa($aId, $adrId, $assessmentRole, $questionId)) {
                    $aqaId = $aqa->aqa_id;
                    $dataAnswer['aqa_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                    $dataAnswer['aqa_update_u_id'] = $identity['u_id'];
                    $this->tableGateway->update($dataAnswer, array('aqa_id' => $aqa->aqa_id));
                } else {
                    $dataAnswer['aqa_a_id'] = $aId;
                    $dataAnswer['aqa_adr_id'] = $adrId;
                    $dataAnswer['aqa_aq_id'] = $questionId;
                    $dataAnswer['aqa_ar_id'] = $assessmentRole;

                    $dataAnswer['aqa_create_date'] = new \Zend\Db\Sql\Expression('NOW()');
                    $dataAnswer['aqa_create_u_id'] = $identity['u_id'];

                    $aqaId = $this->tableGateway->insert($dataAnswer);
                    $aqaId = $this->tableGateway->lastInsertValue;
                }

                if (isset($files['notesFiles'][$questionId]) || ($post['notes'][$questionId] != '')) {
                    // save note to answer
                    $note = new Note();

                    $postNote['note_text'] = $post['notes'][$questionId];
                    $postNote['note_item_type'] = \Note\Model\Note::NOTE_ASSESSMENT_ANSWER;
                    $postNote['note_item_id'] = $aqaId;
                    $postNote['note_subitem_id'] = $adrId;
                    $note->exchangeArray($postNote);

                    if (isset($files['notesFiles'][$questionId])) {
                        $notesFiles = $files['notesFiles'][$questionId];
                    } else {
                        $notesFiles = array();
                    }

                    $this->getServiceLocator()->get('Note\Model\NoteTable')->setServiceLocator($this->getServiceLocator());
                    $this->getServiceLocator()->get('Note\Model\NoteTable')->saveNote($note, array('notesFiles' => $notesFiles));
                }
            }

            $this->deleteNonUsedAqa($aId, $adrId, $assessmentRole, $questionsUsed);

        }

    }



}