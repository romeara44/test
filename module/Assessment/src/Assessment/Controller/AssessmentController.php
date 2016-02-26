<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Assessment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Assessment\Form\AssessmentForm;
use Assessment\Model\Assessment;
use Assessment\Model\AssessmentRoleLocationContact;
use Assessment\Model\AssessmentInventoryLocationItem;
use Assessment\Model\AssessmentBusinessAssociateLocation;
use Assessment\Model\AssessmentQuestionOption;
use Admin\Model\User;
use Note\Model\Note;
use Mail\Model\Mailtemplate;
use Client\Form\ClientForm;
use Businessassociate\Model\Businessassociate;
use Businessassociate\Form\BusinessassociateForm;
use Zend\Session\Container;

class AssessmentController extends AbstractActionController
{
    protected $companyTable;
    protected $businessassociateTable;
    protected $assessmentTable;
    protected $addressTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->searchRoleFilter = 'assessment';
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getCompanyTable()
    {
        if (!$this->companyTable) {
            $sm = $this->getServiceLocator();
            $this->companyTable = $sm->get('Client\Model\CompanyTable');
        }
        return $this->companyTable;
    }

    public function getBusinessassociateTable()
    {
        if (!$this->businessassociateTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateTable = $sm->get('Businessassociate\Model\BusinessassociateTable');
        }
        return $this->businessassociateTable;
    }

    public function getAddressTable()
    {
        if (!$this->addressTable) {
            $sm = $this->getServiceLocator();
            $this->addressTable = $sm->get('Client\Model\AddressTable');
        }
        return $this->addressTable;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getAssessmentTable()
    {
        if (!$this->assessmentTable) {
            $sm = $this->getServiceLocator();
            $this->assessmentTable = $sm->get('Assessment\Model\AssessmentTable');
        }
        return $this->assessmentTable;
    }

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getMailtemplateTable()
    {
        if (!$this->mailtemplateTable) {
            $sm = $this->getServiceLocator();
            $this->mailtemplateTable = $sm->get('Mail\Model\MailtemplateTable');
        }
        return $this->mailtemplateTable;
    }

    public function getCompanyRolesTable()
    {
        if (!isset($this->companyRolesTable)) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
    }
    
    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function listAction()
    {
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $mappingSortCol = array(
            'id' => 'a_id',
            'cName' => 'c_name',
            'status' => '_status',
            'type' => '_type',
            'date' => 'a_create_date',
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'a_id';
        $paginator = $this->getAssessmentTable()->getAssessments(true, $sortCol, $order, $this->getIdentity());
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open assessments list page');

        return $view;
    }

    public function editlistAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $location = (int) $this->params('location');

        if (!$id) return;

        $aObj = $this->getAssessmentTable()->getAssessment($id);
        $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
        $assessmentsRoles = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($aObj->a_type, true);
//var_dump($aObj);die();
        $viewParams['aObj'] = $aObj;
        $viewParams['addresses'] = $addresses;
        $viewParams['assessmentsRoles'] = $assessmentsRoles;
        $viewParams['checkSteps'] = $this->getAssessmentTable()->checkSteps($id, $addresses, $assessmentsRoles);
        $viewParams['location'] = $location;

        $viewModel = new ViewModel($viewParams);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open assessments edit list page "' . $id . '"');

        return $viewModel;

    }

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $step = (int) $this->params('step');
        $step = $step == 0 ? 1 : $step;
        $location = (int) $this->params('location');
        $assessmentRole = 0;
        $companyRolesMsg = '';

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);

        if ($step == 3) {
            $locationRole = explode('_', $this->params('locationRole'));
            $location = isset($locationRole[0]) ? $locationRole[0] : 0;
            $assessmentRole = isset($locationRole[1]) ? $locationRole[1] : 0;
        }

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new AssessmentForm($this->getServiceLocator());

        $aObj = null;
        $contacts = null;
        if ((int) $id) {
            $aObj = $this->getAssessmentTable()->getAssessment($id);
            $aObj->_client_name = stripslashes($aObj->_client_name);
            $contacts = $this->getUserTable()->getUsersByCompany($aObj->a_c_id);
        }

        $addresses = array();
        $identity = $this->getIdentity();
        $request = $this->getRequest();

        $isPrivacy = false;
        $valid = false;
        if ($request->isPost()) {
            $post = $request->getPost();

            if ($step != 1) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);
            }

            if ($step == 1) {
                $a = new Assessment();
                $form->setInputFilter($a->getInputFilter($this->getServiceLocator(), $id));
                $form->setData($request->getPost());

                $isNew = (int) $id ? false : true;

                if(!$checkFillCompanyRoles = $this->getCompanyRolesTable()->checkFillCompanyRoles($post['a_c_id'])) {
                    $companyRolesMsg = 'Please, fill all roles for this company';
                }
                
                if ($form->isValid() && $checkFillCompanyRoles) {
                    $isPrivacy = $post['a_type'] == 2;
                    /*if ($isNew && $isPrivacy) {
                        $isPossible = $this->getAssessmentTable()->isPrivacyCreatePossible($post['a_c_id']);
                        if (!$isPossible) {
                            $this->flashMessenger()->addErrorMessage('You can\'t add privacy assessment for this company');
                            return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
                        }
                    }*/

                    $valid = true;
                    $post['a_owner_u_id'] = $identity['u_id'];
                    $post['a_update_u_id'] = $identity['u_id'];

                    $a->exchangeArray($post);
                    $this->getAssessmentTable()->setServiceLocator($this->getServiceLocator());

                    $oldId = $id;
                    if ($isNew) {
                        $aId = $this->getAssessmentTable()->saveAssessment($a);
                        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $aId);
                        $id = $aId;
                        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new assessment "' . $id . '" step 1');
                    }

                    if (!$isNew && !$isPrivacy) {
                        $this->getAssessmentTable()->saveAddresses($id, $request->getPost());
                        //$this->getAssessmentTable()->copyAdressesToPrivacy($isPossible->a_id, $aId);
                    } else {

                        //$this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update assessment "' . $id . '" step 1');
                        //$newAdressesKeys = $this->getAssessmentTable()->saveAddresses($id, $request->getPost());
                    }


                    //$this->getAssessmentTable()->checkStepFinished($id, 1, $isNew);

                }
            } elseif ($step == 2) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update assessment "' . $id . '" step 2');
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_UPLOADED_ROLES, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);

                $valid = true;
                $post = $request->getPost();

                if (isset($post['arlc'])) {
                    foreach ($post['arlc'] as $locationId => $valueArlc) {
                        foreach ($valueArlc as $arId => $uId) {
                            $arlc = new AssessmentRoleLocationContact();

                            $dataArlc['arlc_a_id'] = $id;
                            $dataArlc['arlc_adr_id'] = $locationId;
                            $dataArlc['arlc_ar_id'] = $arId;
                            $dataArlc['arlc_u_id'] = $uId;

                            $arlc->exchangeArray($dataArlc);
                            $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->saveArlc($arlc);
                        }
                    }
                }
                if ($this->getAssessmentTable()->checkLocationFinished($id, $location)) {
                    $this->getAssessmentTable()->checkAllLocationsFinished($id);
                    $newCreatedRpId = $this->getAssessmentTable()->_createRemediationPlan($id, $location);
                    if ($newCreatedRpId) {
                        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
                    }
                }
            } elseif ($step == 3) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update assessment "' . $id . '" step 5');
                //$id = $this->getAssessmentTable()->cloneAssessment($id);

                $valid = true;
                $post = $request->getPost();
                $adrId = (int) $this->params('adrId');
                $assessmentRole = (int) $this->params('assessmentRole');
                $files = $request->getFiles();

                $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->saveAqa($id, $adrId, $assessmentRole, $post, $files);

                if ($this->getAssessmentTable()->checkLocationFinished($id, $adrId)) {
                    $this->getAssessmentTable()->checkAllLocationsFinished($id);
                    $newCreatedRpId = $this->getAssessmentTable()->_createRemediationPlan($id, $adrId);
                    if ($newCreatedRpId) {
                        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
                    }
                }                
            }

            if ($valid) {
                $this->flashMessenger()->addSuccessMessage('Assessment (step: ' . $step . ') saved');

                if ($isPrivacy) {
                    if ($post['setStep']) {
                        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => 3));
                    }
                }

                if ($post['setStep']) {
                    $currentAdrId = $post['locationHidden'];
                    $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
                    $counterAdr = 0;
                    $lastAdrId = 0;
                    foreach ($addresses->buffer() as $address) {
                        if (!$counterAdr) {
                            $adrId = $adrIdB = $address->adr_id;
                            $counterAdr++;
                        } else {
                            $lastAdrId = $address->adr_id;
                        }
                    }

                    if (!$currentAdrId) {
                        $currentAdrId = (int) $this->params('adrId');
                    }

                    if (!$currentAdrId) {
                        $currentAdrId = $adrId;
                    }
                    $nextAdrId = 0;
                    $c = false;
                    foreach ($addresses->buffer() as $address) {
                        if ($c) {
                            $nextAdrId = $address->adr_id;
                            break;
                        }
                        if ($currentAdrId == $address->adr_id) {
                            $c = true;
                        }
                    }

                    if ($step == 1) {
                        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $post['setStep'], 'location' => $adrId));
                    } elseif ($step == 3) {
                        if ($assessmentRole == 6) {
                            if ($nextAdrId) {
                                return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $post['setStep'], 'locationRole' => $nextAdrId . '_1'));    
                            } else {
                                $assessmentsRoles = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($aObj->a_type, true);
                                $checkSteps = $this->getAssessmentTable()->checkSteps($id, $addresses, $assessmentsRoles);
                                foreach ($checkSteps as $step1 => $finished_array) {
                                    if ($step1 == 1) continue;
                                    if ($step1 == 5) {
                                        foreach ($finished_array as $location => $finished_array2) {
                                            foreach ($finished_array2 as $assesRole => $finished) {
                                                if (!$finished) {
                                                    return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => 3, 'locationRole' => $location . '_' . $assesRole));
                                                    
                                                }
                                            }
                                        }
                                    }  else {
                                        foreach ($finished_array as $location => $finished_value) {
                                            if (!$finished_value) {
                                                return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $step1, 'location' => $location));
                                                                                                
                                            }
                                            
                                        }
                                    }

                                }
                            }
                            
                        } else {
                            return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $post['setStep'], 'locationRole' => $currentAdrId . '_' . ($assessmentRole + 1)));
                        }
                    } else {
                        if ($nextAdrId) {
                            return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $step, 'location' => $nextAdrId));
                        } else {
                            return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => $post['setStep'], 'location' => $adrId));
                        }
                    }
                } else {
                    return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
                }
            }

        } else {
            if ((int) $id) {
                $form->bind($aObj);
                $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::ASSESSMENT_TYPE);

                $counterAdr = 0;
                $lastAdrId = 0;
                foreach ($addresses->buffer() as $address) {
                    if (!$counterAdr) {
                        $adrId = $adrIdB = $address->adr_id;
                        $counterAdr++;
                    } else {
                        $lastAdrId = $address->adr_id;
                    }
                }
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open assessments edit page "' . $id . '" step "' . $step . '"');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new assessments page');
            }
        }

        $companyId = $this->params('companyId');
        $companyTable = $this->getServiceLocator()->get('Client\Model\CompanyTable');
        $company = $companyTable->getCompany($companyId);

        $viewParams = array(
            'form' => $form,
            'aId' => $id,
            'currentStep' => $step,
            'addresses' => $addresses,
            'aObj' => $aObj,
            'step' => $step,
            'location' => $location,
            'companyId' => $companyId,
            'company' => $company,
            'isClosed' => (is_object($aObj) && ($aObj->a_status == 100)) ? true : false,
            'companyAddresses' => array(),
            'lastAdrId' => isset($lastAdrId) ? $lastAdrId : 0,
            'companyRolesMsg' => $companyRolesMsg
        );

        if ($companyId) {
            $companyAddresses = $this->getAddressTable()->getAddresses($companyId, \Client\Model\AddressItem::COMPANY_TYPE);

            $viewParams['companyAddresses'] = $companyAddresses;
        }

        if ($step == 1) {
            $viewParams['locationName'] = $this->getAddressTable()->getLocationNameById($location);
            $viewParams['locationFinished'] = $id ? $this->getAssessmentTable()->checkLocationFinished($id, $location) : 0;
        } elseif ($step == 2) {
            $viewParams['locationFinished'] = $this->getAssessmentTable()->checkLocationFinished($id, $location);
            $viewParams['assessmentsRoles'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($aObj->a_type);
            $viewParams['arlcContacts'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->getArlcByLocation($id, $location);
            $viewParams['companyRoles'] = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getExistsCompanyRoles($aObj->a_c_id);
            $viewParams['contacts'] = $contacts;
            $adrId = 0;
            foreach ($addresses->buffer() as $address) {
                $adrId = $address->adr_id;
                break;
            }
            $viewParams['rolesAdr1'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->getArlcByLocation($id, $adrId);
        } elseif ($step == 3) {
            $viewParams['locationFinished'] = $this->getAssessmentTable()->checkLocationFinished($id, $location);
            $viewParams['assessmentsRoles'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles($aObj->a_type, true);
            $viewParams['assessmentRole'] = $assessmentRole;
            $viewParams['locationName'] = $this->getAddressTable()->getLocationNameById($location);
            $viewParams['assessmentRoleName'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getRoleNameById($assessmentRole);

            $viewParams['questions'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestions($aObj->a_type, $assessmentRole, $aObj->a_id, $location, $aObj);
           // var_dump($viewParams['questions']);die();
            if ($location) {
                $viewParams['answers'] = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionAnswerTable')->getAqas($id, $location, $assessmentRole);
            } else {
                $viewParams['answers'] = array();
            }
        }

        $viewModel = new ViewModel($viewParams);

        $viewModel->setTemplate('assessment/assessment/_editstep' . $step . '.phtml');

        return $viewModel;
    }

    public function invitetoassessmentAction()
    {
        $id = $this->params('id');
        $companyTable = $this->getServiceLocator()->get('Client\Model\CompanyTable');
        $company = $companyTable->getCompany($id);

        if (!$id) {
            die('You have to choose a client');
        }
        if (!$company->c_primary_contact_u_id) {
            die('You have to choose a primary contact for company ' . $company->c_name);
        }

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('invitetoassessment2');

        $contact = $this->getUserTable()->getUser($company->c_primary_contact_u_id);
        $text = $mt->mt_text;
        $identity = $this->getIdentity();

        $text = str_replace('<Client Name>', $contact->u_firstname, $text);
        $text = str_replace('<username>',  $contact->u_email, $text);

        $text = str_replace('<Consultant Name>',  $identity['u_firstname'] . ' ' . $identity['u_firstname'], $text);
        $text = str_replace('<Consultant Title>',  $identity['u_title'], $text);
        $text = str_replace('<Consultant phone>',  $identity['u_office_phone'], $text);
        $text = str_replace('<Consultant email>',  $identity['u_email'], $text);


        $viewModel = new ViewModel(array(
            'title' => str_replace('<Client Name>',  $company->c_name, $mt->mt_name),
            'text' => $text,
            'header_title' => $company->c_name,
            'title_label' => 'Name',
            'subject' => $mt->mt_subject,
            'addto' => $contact->u_email,
            'passwordToSent' => 0,
            'passwordUId' => $company->c_primary_contact_u_id
        ));

        $viewModel->setTemplate('assessment/assessment/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send invive to assessment "' . $id . '"');

        return $viewModel;
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getAssessmentTable()->deleteAssessment($id);
        $this->flashMessenger()->addSuccessMessage('Assessment has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete assessment "' . $id . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getAssessmentTable()->unarchiveAssessment($id);
        $this->flashMessenger()->addSuccessMessage('Assessment has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_ASSESSMENT, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive assessment "' . $id . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
    }

    public function duplicateAction()
    {
        $id = $this->params('id');

        $this->getAssessmentTable()->duplicateAssessment($id);
        $this->flashMessenger()->addSuccessMessage('Assessment has been duplicated');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Dublicate assessment "' . $id . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'list'));
    }

    public function deleteaddressAction()
    {
        $id = $this->params('id');
        $adrId = $this->params('adrId');

        $this->getServiceLocator()->get('Client\Model\AddressTable')->deleteAddress($adrId);
        $this->flashMessenger()->addSuccessMessage('Address has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete assessment "' . $id . '" adrress "' . $adrId . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id));
    }

    public function deleteailiAction()
    {
        $id = $this->params('id');
        $ailiId = $this->params('ailiId');
        $adrId = $this->params('adrId');

        $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->deleteAili($ailiId);
        $this->flashMessenger()->addSuccessMessage('Assessment has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete assessment "' . $id . '" inventory location "' . $ailiId . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => 3, 'location' => $adrId));
    }

    public function deleteabalAction()
    {
        $id = $this->params('id');
        $abalId = $this->params('abalId');
        $adrId = $this->params('adrId');

        $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->deleteAbal($abalId);
        $this->flashMessenger()->addSuccessMessage('Assessment has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete assessment "' . $id . '" business associate location "' . $abalId . '"');

        return $this->redirect()->toRoute('assessment', array('controller' => 'assessment', 'action' => 'edit', 'id' => $id, 'step' => 4, 'location' => $adrId));
    }

    public function addnewcontactAction()
    {
        $aId = $this->params('id');
        $step = $this->params('step');
        $companyId = $this->params('companyId');
        $location = $this->params('location');

        $identity = $this->getIdentity();

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new ClientForm($this->getServiceLocator());

        $clientObj = $this->getCompanyTable()->getCompany($companyId);
        $primaryAddressObj = $this->getAddressTable()->getAddress($clientObj->c_primary_adr_id);

        $added = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();

            $post = $request->getPost();
            $post['u_company_id'] = $companyId;

            $form->setInputFilter($user->getClientInputFilter($this->getServiceLocator()));
            $form->setData($post);

            if ($form->isValid()) {

                $post['u_role_id'] = \Admin\Model\User::ROLE_CLIENT;
                $post['u_senior_consultant_u_id'] = $identity['u_id'];
                $user->exchangeArray($post);
                $uId = $this->getUserTable()->saveUser($user);

                $iisPrimaryContact = isset($post['is_primary_contact']) ? 1 : 0;
                if ($iisPrimaryContact) {
                    $this->getCompanyTable()->setPrimaryContactId($post['u_company_id'], $uId);
                }

                $this->flashMessenger()->addSuccessMessage('Client saved');

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new assessment "' . $aId . '" contact "' . $uId . '"');
                $added = true;
                //return $this->redirect()->toRoute('assessment', array('assessment' => 'client', 'action' => 'edit', 'id' => $aId, 'step' => $step, 'companyId' => $companyId));

            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                   // echo "Validation failure '$messageId': $message\n";
                }
               //die;
            }

        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'uId' => 0,
            'clientObj' => $clientObj,
            'primaryAddressObj' => $primaryAddressObj,
            'aId' => $aId,
            'location' => $location,
            'added' => $added,
            'companyId' => $companyId
        ));

        $viewModel->setTemplate('assessment/assessment/addnewcontactmodal.phtml');

        $viewModel->setTerminal(true);


        return $viewModel;
    }


    public function addnewbaAction()
    {
        $aId = $this->params('id');
        $step = $this->params('step');
        $companyId = $this->params('companyId');
        $location = $this->params('location');

        $identity = $this->getIdentity();

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new BusinessassociateForm($this->getServiceLocator());

        $added = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ba = new Businessassociate();

            $post = $request->getPost();
            $post['ba_c_id'] = $companyId;

            $form->setInputFilter($ba->getInputFilter($this->getServiceLocator()));
            $form->setData($post);

            if ($form->isValid()) {

                $post['ba_consultant_u_id'] = $identity['u_id'];

                $ba->exchangeArray($post);
                $this->getBusinessassociateTable()->setServiceLocator($this->getServiceLocator());
                $baId = $this->getBusinessassociateTable()->saveBusinessassociate($ba);

                // save contact person
                $user = new User();
                $post['u_role_id'] = User::ROLE_BUSINESS_ASSOCIATE;
                $post['u_sent_password'] = 0;
                $user->exchangeArray($post);

                $this->getUserTable()->setServiceLocator($this->getServiceLocator());

                $uId = $this->getUserTable()->saveUser($user);
                $this->getBusinessassociateTable()->setContactId($baId, $uId);

                $this->flashMessenger()->addSuccessMessage('Business associate saved');

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new assessment "' . $aId . '" business associate "' . $uId . '"');
                $added = true;
                //return $this->redirect()->toRoute('assessment', array('assessment' => 'client', 'action' => 'edit', 'id' => $aId, 'step' => $step, 'companyId' => $companyId));

            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                    // echo "Validation failure '$messageId': $message\n";
                }
                //die;
            }

        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'uId' => 0,
            'aId' => $aId,
            'location' => $location,
            'added' => $added,
            'companyId' => $companyId
        ));

        $viewModel->setTemplate('assessment/assessment/addnewbamodal.phtml');

        $viewModel->setTerminal(true);


        return $viewModel;
    }

    public function addanswersAction()
    {
        $questions = $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionTable')->getQuestionsTest();

        $answers = array('Yes', 'No', 'N/A');
        foreach ($questions as $q) {
            $aqo = new AssessmentQuestionOption();
            $data = array();

            $data['aqo_aq_id'] = $q->aq_id;
            $order = 1;
            foreach ($answers as $a) {
                $data['aqo_title'] = $a;
                $data['aqo_order'] = $order;
                $data['aqo_risk_score'] = 0;

                $aqo->exchangeArray($data);

                $this->getServiceLocator()->get('Assessment\Model\AssessmentQuestionOptionTable')->saveOption($aqo);
                $order++;
            }
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add assessment answers');

        die;
    }

    public function resetactivityAction()
    {
        $container = new Container('activity');
        $container->activity = time();

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Reset assessment activity');

        return $this->getResponse()->setContent(1);
    }

}
