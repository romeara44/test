<?php
namespace Assessment\Model;

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
use Zend\Db\Sql\Predicate\PredicateSet;

class AssessmentTable implements ServiceLocatorAwareInterface
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

    public function getAssessments($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('assessments');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Assessment());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN) {
            } else {
                $where_str = '';
                if ($identity['u_role_id'] == \Admin\Model\User::ROLE_CONSULTANT) {
                    $where_str .= 'a_owner_u_id = ' . $identity['u_id'];
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $where_str .= 'a_consultant_u_id IN (' . implode(',', $ids) . ')';
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
                    $where_str .= 'a_c_id = ' . $identity['u_company_id'];
                } elseif ($identity['u_role_id'] == User::ROLE_TRAIL && $identity['u_company_id']) {
                    $where_str .= 'a_c_id = ' . $identity['u_company_id'];
                }

                $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
                if ($companies_ids) {
                    if ($where_str) {
                        $where_str = '(' . $where_str . ' OR a_c_id IN (' . implode(',', $companies_ids) . '))';
                      } else {
                        $where_str = 'a_c_id IN (' . implode(',', $companies_ids) . ')';
                      }
                }
                if ($where_str) {
                    $where_str .= ' AND a_active = 1';
                } else {
                    $where_str = 'a_active = 1';
                }
                $select->where($where_str);
            }

            $select->join(array('c' => 'companies'), 'a_c_id = c_id', array('_client_name' => 'c_name'), 'left');
            $select->columns(array( '*'
                                  , '_status' => new \Zend\Db\Sql\Expression('IF(assessments.a_status = ' . Assessment::STATUS_INPROGRESS . ', "' . Assessment::$statusesNames[Assessment::STATUS_INPROGRESS] . '", "' . Assessment::$statusesNames[Assessment::STATUS_CLOSED] . '")')
                                  , '_type' => new \Zend\Db\Sql\Expression('IF(assessments.a_type = ' . Assessment::TYPE_SECURITY_RISK . ', "' . Assessment::$typesNames[Assessment::TYPE_SECURITY_RISK] . '", "' . Assessment::$typesNames[Assessment::TYPE_PRIVACY_RISK] . '")')
                                  )
                            );

            $select->where('c_active = 1');

            $order = $order ? $order : 'ASC';

            if ($orderBy) {
                $orders[] = $orderBy . ' ' . $order;
            }

            $orders[] = 'a_version_index ' . $order;
            $orders[] = 'a_id ASC';

            $select->order($orders);

            $paginator = new Paginator($paginatorAdapter);


            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('c_name LIKE "%' . $searchValue . '%"');

        $select->columns(array('_id' => new \Zend\Db\Sql\Expression('a_id'),
                               '_name' => new \Zend\Db\Sql\Expression('c_name'),
                               '_item_type' => new \Zend\Db\Sql\Expression('a_type'),
                               '_type' => new \Zend\Db\Sql\Expression('CONCAT("assessment")'),
                               '_status' => new \Zend\Db\Sql\Expression('a_status'),
                               '_date' => new \Zend\Db\Sql\Expression('a_create_date'),
                               '_active' => 'a_active',
                               )
                        );
        $select->join(array('c' => 'companies'), 'a_c_id = c_id', array(), 'left');

        $where_str = '';
        if ($identity['u_role_id'] == \Admin\Model\User::ROLE_CONSULTANT) {
            $where_str .= 'a_owner_u_id = ' . $identity['u_id'];
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $where_str .= 'a_consultant_u_id IN (' . implode(',', $ids) . ')';
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
            $where_str .= 'a_c_id = ' . $identity['u_company_id'];
        }

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
            if ($companies_ids) {
                if ($where_str) {
                    $where_str = '(' . $where_str . ' OR a_c_id IN (' . implode(',', $companies_ids) . '))';
                  } else {
                    $where_str = 'a_c_id IN (' . implode(',', $companies_ids) . ')';
                  }
            }
            if ($where_str) {
                $where_str .= ' AND a_active = 1';
            } else {
                $where_str = 'a_active = 1';
            }
        }
        if ($where_str) {
            $select->where($where_str);
        }

        $select->where('c_active = 1');

        return $select;
    }

    public function getForReport($conditionNum = 0, $status = 10, $uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('a_active = 1');
        $condition = isset(\Admin\Model\UserTable::$reportCondition[$conditionNum]) ? \Admin\Model\UserTable::$reportCondition[$conditionNum] : null;

        if ($condition != '') {
            $condition = str_replace('?', 'a_create_date', $condition);
            $select->where($condition);
        }

        $select->columns(array('_client_name' => new \Zend\Db\Sql\Expression('COUNT(a_id)')));

        $select->where("a_status = $status");

        if ($uId) {
            $identity = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($uId);

            if ($identity->u_role_id == User::ROLE_CONSULTANT) {
                $select->where('a_consultant_u_id = ' . $identity->u_id);
            } elseif ($identity->u_role_id == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity->u_id);
                $ids[] = $identity->u_id;
                $select->where('a_consultant_u_id IN (' . implode(',', $ids) . ')');
            }
        }


        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return ($row->_client_name);
    }

    public function getForImport()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('a_type = ' . Assessment::TYPE_SECURITY_RISK);
        //$select->where('a_id = 110');
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAssessment($id)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('a_id = ' . $id);

        if ($identity['u_role_id'] != \Admin\Model\User::ROLE_ADMIN) {
            $select->where('a_active = 1');
        }

        $select->join(array('c' => 'companies'), 'a_c_id = c_id', array('_client_name' => 'c_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAssessmentByContactId($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('ba_contact_u_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAssessment(Assessment $a, $step = 1, $clone = false)
    {
        $id = (int) $a->a_id;

        $data = array();
        if ($step == 1) {
            if (!$id) {
                $data['a_c_id'] = $a->a_c_id;
                $data['a_type'] = $a->a_type;
                $data['a_status'] = Assessment::STATUS_INPROGRESS;
            }
        }

        $data['a_parent_a_id'] = $a->a_parent_a_id;
        $data['a_version_index'] = $a->a_version_index;
        $data['a_is_version'] = $a->a_is_version;

        if ($clone) {
            $data['a_step1_finished'] = $a->a_step1_finished;
            $data['a_step2_finished'] = $a->a_step2_finished;
            $data['a_step3_finished'] = $a->a_step3_finished;
            $data['a_step4_finished'] = $a->a_step4_finished;
            $data['a_step5_finished'] = $a->a_step5_finished;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);
        }

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!$id) {
            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['a_consultant_u_id'] = $identity['u_id'];
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['a_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['a_c_id'] = $identity['u_company_id'];
            }

            $data['a_owner_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            if ($data['a_type'] == 2) { // if privacy
                $aSec = $this->isPrivacyCreatePossible($data['a_c_id']);
                if ($aSec) {
                    if ((int) $aSec->a_parent_a_id){
                        $data['a_security_a_id'] = $aSec->a_parent_a_id;
                    } else {
                        $data['a_security_a_id'] = $aSec->a_id;
                    }
                } else {
                    $data['a_security_a_id'] = 0;
                }
                
            }

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            if (!$clone) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);
            }

            if (!(int) $a->a_version_index) {
                $this->tableGateway->update(array('a_version_index' => $id), array('a_id' => $id));
            }
            if (!$clone) {
                $this->copyAdresses($id, $data['a_c_id'], $data['a_type']);
            }
        } else {
            if ($this->getAssessment($id)) {
                $data['a_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                $data['a_update_u_id'] = $identity['u_id'];

                $this->tableGateway->update($data, array('a_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        if ((int) $data['a_parent_a_id']) {
            $this->reindexVersion($a->a_version_index);
        }
        return $id;
    }

    public function reindexVersion($versionIndex)
    {
        $versionIndex  = (int) $versionIndex;

        $select = $this->tableGateway->getSql()->select();
        $select->where('a_version_index = ' . $versionIndex);
        $select->where('a_active = 1');
        $select->where('a_parent_a_id IS NOT NULL');

        $select->order('a_id ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        $indexItem = 1;
        foreach ($resultSet as $rs) {
            $data = array(
                'a_id' => $rs->a_id,
                'a_version_index_item' => $indexItem,
            );

            $this->tableGateway->update(array('a_version_index_item' => $indexItem), array('a_id' => $rs->a_id));
            $indexItem++;

        }
    }

    public function saveAddresses($aId, $post, $isClone = false)
    {
        // save edit addresses
        $counterExistsAdr = 0;
        if (isset($post['exists_adr_address1'])) {
            foreach ($post['exists_adr_address1'] as $keyAdr => $adr) {
                $addressData = array();
                $addressItemData = array();

                if (trim($post['exists_adr_address1'][$keyAdr]) == '') continue;

                $addressData['adr_address1'] = $post['exists_adr_address1'][$keyAdr];
                $addressData['adr_address2'] = $post['exists_adr_address2'][$keyAdr];
                $addressData['adr_city'] = $post['exists_adr_city'][$keyAdr];
                $addressData['adr_state_id'] = $post['exists_adr_state_id'][$keyAdr];
                $addressData['adr_zip'] = $post['exists_adr_zip'][$keyAdr];
                $addressData['adr_name'] = $post['exists_adr_name'][$keyAdr];
                $addressData['adr_phone'] = $post['exists_adr_phone'][$keyAdr];
                $addressData['adr_phone_inner'] = $post['exists_adr_phone_inner'][$keyAdr];
                $addressData['adr_other_phone'] = $post['exists_adr_other_phone'][$keyAdr];
                $addressData['adr_other_phone_inner'] = $post['exists_adr_other_phone_inner'][$keyAdr];
                $addressData['adr_fax'] = $post['exists_adr_fax'][$keyAdr];
                $addressData['adr_email'] = $post['exists_adr_email'][$keyAdr];

                if (!$isClone) {
                    $addressData['adr_id'] = $keyAdr;
                }

                // save to address table
                $sm = $this->getServiceLocator();
                $addressTable = $sm->get('Client\Model\AddressTable');

                $address = new \Client\Model\Address();
                $address->exchangeArray($addressData);

                $addressId = $addressTable->saveAddress($address);

                $newAdressesKeys[$keyAdr] = $addressId;

                if ($isClone) {
                    // save to address item table
                    $addressItem = new \Client\Model\AddressItem();

                    $addressItemData['cadr_type'] = $addressItem::ASSESSMENT_TYPE;
                    $addressItemData['cadr_c_id'] = $aId;
                    $addressItemData['cadr_adr_id'] = $addressId;

                    $sm = $this->getServiceLocator();
                    $addressItemTable = $sm->get('Client\Model\AddressItemTable');

                    $addressItem->exchangeArray($addressItemData);

                    $addressItemId = $addressItemTable->saveAddressItem($addressItem);
                }
                $counterExistsAdr++;
            }
        }

        // save new addresses
        $counterAdr = 0;
        if (isset($post['adr_address1'])) {
            foreach ($post['adr_address1'] as $keyAdr => $adr) {
                $addressData = array();
                $addressItemData = array();

                if (trim($post['adr_address1'][$keyAdr]) == '') continue;

                $addressData['adr_address1'] = $post['adr_address1'][$keyAdr];
                $addressData['adr_address2'] = $post['adr_address2'][$keyAdr];
                $addressData['adr_city'] = $post['adr_city'][$keyAdr];
                $addressData['adr_state_id'] = $post['adr_state_id'][$keyAdr];
                $addressData['adr_zip'] = $post['adr_zip'][$keyAdr];
                $addressData['adr_name'] = $post['adr_name'][$keyAdr];
                $addressData['adr_phone'] = $post['adr_phone'][$keyAdr];
                $addressData['adr_phone_inner'] = $post['adr_phone_inner'][$keyAdr];
                $addressData['adr_other_phone'] = $post['adr_other_phone'][$keyAdr];
                $addressData['adr_other_phone_inner'] = $post['adr_other_phone_inner'][$keyAdr];
                $addressData['adr_fax'] = $post['adr_fax'][$keyAdr];
                $addressData['adr_email'] = $post['adr_email'][$keyAdr];

                // save to address table
                $sm = $this->getServiceLocator();
                $addressTable = $sm->get('Client\Model\AddressTable');

                $address = new \Client\Model\Address();
                $address->exchangeArray($addressData);

                $addressId = $addressTable->saveAddress($address);

                // save to address item table
                $addressItem = new \Client\Model\AddressItem();

                $addressItemData['cadr_type'] = $addressItem::ASSESSMENT_TYPE;
                $addressItemData['cadr_c_id'] = $aId;
                $addressItemData['cadr_adr_id'] = $addressId;

                $sm = $this->getServiceLocator();
                $addressItemTable = $sm->get('Client\Model\AddressItemTable');

                $addressItem->exchangeArray($addressItemData);

                $addressItemId = $addressItemTable->saveAddressItem($addressItem);

                $counterAdr++;
            }
        }


        return isset($newAdressesKeys) ? $newAdressesKeys : array();
    }

    public function deleteAssessment($id)
    {
        $data['a_id'] = $id;
        $data['a_active'] = 0;
        $this->tableGateway->update($data, array('a_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);

        return true;
    }

    public function unarchiveAssessment($id)
    {
        $data['a_id'] = $id;
        $data['a_active'] = 1;
        $this->tableGateway->update($data, array('a_id' => $id));

        return true;
    }

    public function deleteAssessmentsByCompanyId($cId, $value = 0)
    {
        $data['a_active'] = $value;
        $this->tableGateway->update($data, array('a_c_id' => $cId));

        return true;
    }

    public function checkAllLocationsFinished($id)
    {
        $a = $this->getAssessment($id);

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
        $assessmentsRoles = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($a->a_type);

        if (!count($addresses)) {
            return false;
        }

        if ($a->a_type == 1) {
            foreach ($addresses->buffer() as $address) {
                if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->checkStep($id, $address->adr_id)) {
                    return false;
                }
            }
        }        

        foreach ($addresses->buffer() as $address) {
            foreach ($assessmentsRoles->buffer() as $ar) {
                $questions = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestions($a->a_type, $ar->ar_id, $a->a_id, $address->adr_id, $a);
                if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->checkStep($id, $address->adr_id, $ar->ar_id, $questions)) {
                    return false;
                }

            }
        }

        $data['a_all_steps_finished'] = 1;
        $data['a_status'] = 100;
        $this->tableGateway->update($data, array('a_id' => $id));

        return true;
    }

    public function checkLocationFinished($id, $location)
    {
        $a = $this->getAssessment($id);

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
        $assessmentsRoles = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($a->a_type);

        if (!count($addresses)) {
            return false;
        }

        if ($a->a_type == 1 && !(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->checkStep($id, $location)) {
            return false;
        }

        foreach ($assessmentsRoles->buffer() as $ar) {
            $questions = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestions($a->a_type, $ar->ar_id, $a->a_id, $location, $a);
            if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->checkStep($id, $location, $ar->ar_id, $questions)) {
                return false;
            }

        }

        return true;
    }


    public function checkStepFinished($id, $stepNum, $isNew = false)
    {
        $a = $this->getAssessment($id);

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
        $assessmentsRoles = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($a->a_type);

        if ($stepNum == 1) {
            $value = 0;
            if (count($addresses)) {
                $value = 1;
            }
        } elseif ($stepNum == 2) {
            $value = 1;
            foreach ($addresses->buffer() as $address) {
                if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->checkStep($id, $address->adr_id)) {
                    $value = 0;
                    break;
                }
            }
        } elseif ($stepNum == 3) {
            $value = 1;
            foreach ($addresses->buffer() as $address) {
                if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->checkStep($id, $address->adr_id)) {
                    $value = 0;
                    break;
                }
            }
        } elseif ($stepNum == 4) {
            $value = 1;
            foreach ($addresses->buffer() as $address) {
                if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->checkStep($id, $address->adr_id)) {
                    $value = 0;
                    break;
                }
            }
        } elseif ($stepNum == 5) {
            $value = 1;
            $a = $this->getAssessment($id);

            foreach ($addresses->buffer() as $address) {
                foreach ($assessmentsRoles->buffer() as $ar) {
                    $questions = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestions($a->a_type, $ar->ar_id, $a->a_id, $address->adr_id, $a);
                    if (!(int) $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->checkStep($id, $address->adr_id, $ar->ar_id, $questions)) {
                        $value = 0;
                        break;
                    }

                }
            }
        }

        return $this->_setStepFinished($id, $stepNum, $value);
    }

    private function _setStepFinished($id, $stepNum, $value)
    {
        $data['a_step' . $stepNum . '_finished'] = $value;
        $this->tableGateway->update($data, array('a_id' => $id));

        return $this->_checkAllStepsFinished($id);
    }

    private function _checkAllStepsFinished($id)
    {
        $a = (array) $this->getAssessment($id);

        $stepsAllFinished = 1;

        if ($a['a_type'] == 1) {
            for ($i = 1; $i <= 5; $i++) {
                if ($i == 3 || $i == 4) continue;
                if (!$a['a_step' . $i . '_finished']) {
                    $stepsAllFinished = 0;
                }
            }
        } else {
            if (!$a['a_step5_finished']) {
                $stepsAllFinished = 0;
            }
        }

        if ($stepsAllFinished) {
            $data['a_all_steps_finished'] = 1;
            $data['a_status'] = 100;
            $this->tableGateway->update($data, array('a_id' => $id));

            return $this->_createRemediationPlan($id);
        }
        return 0;
    }


    public function duplicateAssessment($id)
    {
        $a = $this->getAssessment($id);

        $stepNum = 0;
        $afterCreateAddresses = 0;

        if($a->a_step5_finished) {
            $stepNum = 5;
        } else if($a->a_step4_finished) {
            $stepNum = 4;
        } else if($a->a_step3_finished) {
            $stepNum = 3;
        } else if($a->a_step2_finished) {
            $stepNum = 2;
        } else if($a->a_step1_finished) {
            $stepNum = 1;
            $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
            $afterCreateAddresses = $addresses->count() ? $id : 0;
        }

        $cloneObj = $this->cloneAssessment($id, $stepNum, 0, false, $afterCreateAddresses);
    }

    public function cloneAssessment($id, $stepNum = 1, $locationId = 0, $isNewAili = false, $afterCreateAddresses = false, $newAdressesKeysParam = array(), $duplicate = false)
    {
        $a = $this->getAssessment($id);

        if (!$duplicate) {
            $this->tableGateway->update(array('a_writable' => 0, 'a_status' => \Assessment\Model\Assessment::STATUS_CLOSED), array('a_id' => $id));
        }

        // create new row
        $a->a_id = 0;
        if (!$duplicate) {
            $a->a_parent_a_id = $id;
        } else {
            $a->a_step2_finished = 0;
            $a->a_step3_finished = 0;
            $a->a_step4_finished = 0;
            $a->a_step5_finished = 0;
            $a->a_all_steps_finished = 0;
        }

        $a->a_status = \Assessment\Model\Assessment::STATUS_INPROGRESS;

        $newId = $this->saveAssessment($a, 1, true);

        if (!$afterCreateAddresses && ($stepNum == 1)) {
            return $newId;
        }

        $newAdressesKeys = array();

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);

        foreach ($addresses->buffer() as $address) {
            $oldAddressId = $address->adr_id;
            // save to address table
            $address->adr_id = 0;
            $address->adr_create_date = new \Zend\Db\Sql\Expression('NOW()');

            $addressTable = $this->getServiceLocator()->get('Client\Model\AddressTable');
            $addressId = $addressTable->saveAddress($address);

            $newAdressesKeys[$oldAddressId] = $addressId;

            // save to address item table
            $addressItem = new \Client\Model\AddressItem();

            $addressItemData['cadr_type'] = $addressItem::ASSESSMENT_TYPE;
            $addressItemData['cadr_c_id'] = $newId;
            $addressItemData['cadr_adr_id'] = $addressId;

            $addressItemTable = $this->getServiceLocator()->get('Client\Model\AddressItemTable');
            $addressItem->exchangeArray($addressItemData);

            $addressItemId = $addressItemTable->saveAddressItem($addressItem);
        }


        if (!$duplicate) {
            // step 2
            foreach ($addresses->buffer() as $address) {
                if (($address->adr_id == $locationId) && ($stepNum == 2)) {
                    continue;
                }

                $arlcs = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->getArlcsByLocation($id, $address->adr_id);
                foreach ($arlcs as $arlc) {
                    $arlc->arlc_id = 0;
                    $arlc->arlc_create_date = new \Zend\Db\Sql\Expression('NOW()');
                    $arlc->arlc_adr_id = $newAdressesKeys[$address->adr_id];
                    $arlc->arlc_a_id = $newId;

                    $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->saveArlc($arlc);
                }
            }

            // step 3
            foreach ($addresses->buffer() as $address) {
                $reportsFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getAilrByLocation($id, $address->adr_id);

                foreach ($reportsFiles as $rfiles) {
                    foreach ($rfiles as $rf) {
                        $rf->ailr_id = 0;
                        $rf->ailr_create_date = new \Zend\Db\Sql\Expression('NOW()');
                        $rf->ailr_adr_id = $newAdressesKeys[$address->adr_id];
                        $rf->ailr_a_id = $newId;

                        $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->saveAilrWithoutFiles($rf);
                    }
                }

                // copy notes with files
                $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
                $notes = $noteDb->getNotes($id, \Note\Model\Note::NOTE_AILI, $address->adr_id);
                foreach ($notes as $note) {
                    $oldNoteId = $note->note_id;
                    $note->note_id = 0;
                    $note->note_item_id = $newId;
                    $note->note_subitem_id = $newAdressesKeys[$address->adr_id];
                    $noteId = $noteDb->saveNote($note, array(), true);

                    // copy files to notes
                    $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
                    $files = $noteFilesDb->getFilesByNoteId($oldNoteId);

                    foreach ($files as $file) {
                        $dataFile = array();
                        $dataFile['nf_f_id'] = $file['nf_f_id'];
                        $dataFile['nf_note_id'] = $noteId;

                        $noteFilesDb->saveFile($dataFile);
                    }
                }

                if (($address->adr_id == $locationId) && ($stepNum == 3) && !$isNewAili) {
                    continue;
                }

                $ailis = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->getAilisByLocation($id, $address->adr_id);

                foreach ($ailis as $aili) {
                    $aili->aili_id = 0;
                    $aili->aili_create_date = new \Zend\Db\Sql\Expression('NOW()');
                    $aili->aili_adr_id = $newAdressesKeys[$address->adr_id];
                    $aili->aili_a_id = $newId;

                    $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->saveAili($aili);
                }
            }

            // step 4
            foreach ($addresses->buffer() as $address) {
                $reportsFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getReportsFiles($id, $address->adr_id, 5);

                foreach ($reportsFiles as $rf) {
                    $rf->ailr_id = 0;
                    $rf->ailr_create_date = new \Zend\Db\Sql\Expression('NOW()');
                    $rf->ailr_adr_id = $newAdressesKeys[$address->adr_id];
                    $rf->ailr_a_id = $newId;

                    //$this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->saveAilrWithoutFiles($rf);
                }

                // copy notes with files
                $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
                $notes = $noteDb->getNotes($id, \Note\Model\Note::NOTE_ABAL, $address->adr_id);
                foreach ($notes as $note) {
                    $oldNoteId = $note->note_id;
                    $note->note_id = 0;
                    $note->note_item_id = $newId;
                    $note->note_subitem_id = $newAdressesKeys[$address->adr_id];
                    $noteId = $noteDb->saveNote($note, array(), true);

                    // copy files to notes
                    $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
                    $files = $noteFilesDb->getFilesByNoteId($oldNoteId);

                    foreach ($files as $file) {
                        $dataFile = array();
                        $dataFile['nf_f_id'] = $file['nf_f_id'];
                        $dataFile['nf_note_id'] = $noteId;

                        $noteFilesDb->saveFile($dataFile);
                    }
                }

                if (($address->adr_id == $locationId) && ($stepNum == 4) && !$isNewAili) {
                    //continue;
                }

                $abals = $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->getAbalsByLocation($id, $address->adr_id);

                foreach ($abals as $abal) {
                    $abal->abal_id = 0;
                    $abal->abal_create_date = new \Zend\Db\Sql\Expression('NOW()');
                    $abal->abal_adr_id = $newAdressesKeys[$address->adr_id];
                    $abal->abal_a_id = $newId;

                    $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->saveAbal($abal);
                }
            }

            // step 5
            foreach ($addresses->buffer() as $address) {
                $answersByA = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->getAqasByAssessment($id, $address->adr_id);

                foreach ($answersByA as $answer) {
                    $notes = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNotes($answer->aqa_id, \Note\Model\Note::NOTE_ASSESSMENT_ANSWER, $address->adr_id);

                    $answer->aqa_id = 0;
                    $answer->aqa_create_date = new \Zend\Db\Sql\Expression('NOW()');
                    $answer->aqa_adr_id = $newAdressesKeys[$address->adr_id];
                    $answer->aqa_a_id = $newId;

                    $answerId = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->saveAqaByObj($answer);

                    /// NOTES
                    foreach ($notes as $note) {
                        $oldNoteId = $note->note_id;
                        $note->note_id = 0;
                        $note->note_item_id = $answerId;
                        $note->note_subitem_id = $newAdressesKeys[$address->adr_id];
                        $noteId = $noteDb->saveNote($note, array(), true);

                        // copy files to notes
                        $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
                        $files = $noteFilesDb->getFilesByNoteId($oldNoteId);

                        foreach ($files as $file) {
                            $dataFile = array();
                            $dataFile['nf_f_id'] = $file['nf_f_id'];
                            $dataFile['nf_note_id'] = $noteId;

                            $noteFilesDb->saveFile($dataFile);
                        }
                    }
                }
            }
        }

        return array($newId, $newAdressesKeys);
    }

    public function checkSteps($id, $addresses, $assessmentsRoles)
    {
        $a = $this->getAssessment($id);

        $steps[1] = count($addresses) ? true : false;

        foreach ($addresses->buffer() as $address) {
            $steps[2][$address->adr_id] = (int) $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->checkStep($id, $address->adr_id);
        }
/*
        foreach ($addresses->buffer() as $address) {
            $steps[3][$address->adr_id] = (int) $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->checkStep($id, $address->adr_id);
        }

        foreach ($addresses->buffer() as $address) {
            $steps[4][$address->adr_id] = (int) $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->checkStep($id, $address->adr_id);
        }
*/
        foreach ($addresses->buffer() as $address) {
            foreach ($assessmentsRoles->buffer() as $ar) {
                $questions = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestions($a->a_type, $ar->ar_id, $a->a_id, $address->adr_id, $a);
                $steps[5][$address->adr_id][$ar->ar_id] = (int) $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->checkStep($id, $address->adr_id, $ar->ar_id, $questions);
            }
        }

        return $steps;
    }

    public function isPrivacyCreatePossible($cId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('a_c_id = ' . $cId);
        $select->where('a_type = 1');
        //$select->where('DATE_FORMAT(a_create_date, "%Y") = "' . date("Y") . '"');

        $select->where('a_active = 1');
        $select->order('a_id DESC');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function copyAdresses($aId, $cId, $a_type = 2)
    {
        /*$select = $this->tableGateway->getSql()->select();
        $select->where('a_c_id = ' . $cId);
        $select->where('a_type = ' . $a_type);
        $select->where('DATE_FORMAT(a_create_date, "%Y") = "' . date("Y") . '"');

        $select->order('a_id DESC');
        $select->where('a_active = 1');
        $select->limit(1);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();*/

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($cId, \Client\Model\AddressItem::COMPANY_TYPE);
        $i = 1;
        foreach ($addresses->buffer() as $address) {
            $oldAddressId = $address->adr_id;
            // save to address table
            $address->adr_id = 0;
            $address->adr_create_date = new \Zend\Db\Sql\Expression('NOW()');

            $addressTable = $this->getServiceLocator()->get('Client\Model\AddressTable');
            $addressId = $addressTable->saveAddress($address);

            // save to address item table
            $addressItem = new \Client\Model\AddressItem();

            $addressItemData['cadr_type'] = $addressItem::ASSESSMENT_TYPE;
            $addressItemData['cadr_c_id'] = $aId;
            $addressItemData['cadr_adr_id'] = $addressId;

            $addressItemTable = $this->getServiceLocator()->get('Client\Model\AddressItemTable');
            $addressItem->exchangeArray($addressItemData);

            $addressItemId = $addressItemTable->saveAddressItem($addressItem);
            if ($a_type == 2)  break;
        }

        return true;
    }

    public function copyAdressesToPrivacy($aSecurityId, $aPrivacyId)
    {
        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($aSecurityId, \Client\Model\AddressItem::ASSESSMENT_TYPE);

        foreach ($addresses->buffer() as $address) {
            $oldAddressId = $address->adr_id;
            // save to address table
            $address->adr_id = 0;
            $address->adr_create_date = new \Zend\Db\Sql\Expression('NOW()');

            $addressTable = $this->getServiceLocator()->get('Client\Model\AddressTable');
            $addressId = $addressTable->saveAddress($address);

            // save to address item table
            $addressItem = new \Client\Model\AddressItem();

            $addressItemData['cadr_type'] = $addressItem::ASSESSMENT_TYPE;
            $addressItemData['cadr_c_id'] = $aPrivacyId;
            $addressItemData['cadr_adr_id'] = $addressId;

            $addressItemTable = $this->getServiceLocator()->get('Client\Model\AddressItemTable');
            $addressItem->exchangeArray($addressItemData);

            $addressItemId = $addressItemTable->saveAddressItem($addressItem);
        }

        return true;
    }

    public function _createRemediationPlan($aId, $location)
    {
        $aDb = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable');
        $a = $aDb->getAssessment($aId);

        /*
         *
         * $aSec = $this->isPrivacyCreatePossible($data['a_c_id']);
                if ((int) $aSec->a_parent_a_id){
                    $data['a_security_a_id'] = $aSec->a_parent_a_id;
                } else {
                    $data['a_security_a_id'] = $aSec->a_id;
                }*/


        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $dataRp = array();
        $dataRp['rp_a_id'] = $aId;
        $dataRp['rp_c_id'] = $a->a_c_id;
        $dataRp['rp_type'] = $a->a_type;
        $dataRp['rp_consultant_u_id'] = $a->a_consultant_u_id;
        $dataRp['rp_performed_u_id'] = $identity['u_id'];
        $dataRp['rp_remediation_date'] = new \Zend\Db\Sql\Expression('NOW()');
        $dataRp['rp_incident_date'] = $a->a_create_date;   
        $dataRp['rp_adr_id'] = $location;     

        $rpDb = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable');
        $rpDb->setServiceLocator($this->getServiceLocator());
        
        $catsDb = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionCategoryTable');        

        $company = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompany($a->a_c_id);
        $approval_authority_role = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getCompanyRoleByCompanyAndRole($a->a_c_id, 9);

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($aId, \Client\Model\AddressItem::ASSESSMENT_TYPE);
        foreach ($addresses->buffer() as $addressKey => $address) {
            if ($address->adr_id != $location) continue;
            $cats = $catsDb->getCategoriesByType($a->a_type, $addressKey, $company);
            if ($a->a_security_a_id) {
                $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->setServiceLocator($this->getServiceLocator());
                $rp = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplanByAId($a->a_security_a_id);
                if (is_object($rp)) {
                    $dataRp['rp_security_rp_id'] = $rp->rp_id;
                }
            }

            $rp = new Remediationplan();
            $rp->exchangeArray($dataRp);            
            $rpId = $rpDb->saveRemediationplan($rp);
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_RP, $rpId);

            $arlc = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->getArlcByLocation($aId, $address->adr_id);

            foreach ($cats->buffer() as $catKey => $cat) {
                $rpa = new Remediationplanaction();

                $answerScore = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->getAnswersScore($aId, $cat->aqc_id, $address->adr_id);
                $task = $cat->aqc_citation;

                if ($cat->aqc_specification) {
                    if ($task) {
                        $task .= '-';
                    }
                    $task .= $cat->aqc_specification;
                }
                if ($cat->aqc_description) {
                    if ($task) {
                        $task .= '-';
                    }
                    $task .= $cat->aqc_description;
                }

                if ($task == '') {
                    continue;
                }
                $rpaData['rpa_rp_id'] = $rpId;

                if (empty($arlc[$cat->aqc_ar_id])) {
                    $role = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getCompanyRoleByCompanyAndRole($a->a_c_id, $cat->aqc_ar_id);
                    if ($role) {
                        $rpaData['rpa_contact_u_id'] = $role->cr_u_id;
                    } else {
                        $rpaData['rpa_contact_u_id'] = 0;
                    }                    
                } else {
                    $rpaData['rpa_contact_u_id'] = $arlc[$cat->aqc_ar_id];
                }
                
                $rpaData['rpa_approver_u_id'] = $approval_authority_role->cr_u_id;

                $rpaData['rpa_threat'] = $task;
                $rpaData['rpa_risk_score'] = $answerScore->_score;
                $rpaData['rpa_action_plan'] = $cat->aqc_action_plan;
                $rpaData['rpa_policy'] = $cat->aqc_policy;
                $rpaData['rpa_status'] = 0;
                $rpaData['rpa_adr_id'] = $address->adr_id;
                $rpaData['rpa_aqc_id'] = $cat->aqc_id;

                $riskLevel = 0;
                if (($answerScore->_score >= 6) && ($answerScore->_score < 10)) {
                    $riskLevel = 1;
                } elseif ($answerScore->_score >= 10) {
                    $riskLevel = 2;
                }

                if ($answerScore->aqa_aqo_id == 3) {
                    $riskLevel = 3;
                }
                $rpaData['rpa_risk_level'] = $riskLevel;

                if ($riskLevel == 0) {
                    $rpaData['rpa_target_date'] = new \Zend\Db\Sql\Expression('NOW() + INTERVAL 180 DAY');
                } elseif ($riskLevel == 1) {
                    $rpaData['rpa_target_date'] = new \Zend\Db\Sql\Expression('NOW() + INTERVAL 120 DAY');
                } elseif ($riskLevel == 2) {
                    $rpaData['rpa_target_date'] = new \Zend\Db\Sql\Expression('NOW() + INTERVAL 90 DAY');
                }

                $rpa->exchangeArray($rpaData);

                $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable')->setServiceLocator($this->getServiceLocator());
                $rpaId = $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable')->saveRemediationplanaction($rpa);
            }
        }

        return $rpId;
        //die;
    }
}