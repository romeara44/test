<?php
// Filename: /module/Traininglog/src/Traininglog/Controller/TraininglogController.php

namespace Traininglog\Controller;

use Traininglog\Service\TovutiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Admin\Model\User;
use Client\Model\CompanyTable;
use Traininglog\Model\Regulation;
use Traininglog\Model\Traininglogtype;
use Traininglog\Model\Traininglog;
use Traininglog\Model\LitmosUser;
use Traininglog\Model\TraineeUser;

use Traininglog\Form\EmployeemasterlistForm;
use Traininglog\Form\EditTovutiUserForm;
use Traininglog\Form\TraininglogForm;
use Traininglog\Form\TovutiUserForm;

use Traininglog\Model\TovutiUser;
use Traininglog\Model\TovutiUserGroup;

class TraininglogController extends AbstractActionController
{
    //protected $userTable;
    protected $companyTable;
    private $form;

    /**
     * @var \Traininglog\Service\TovutiServiceInterface
     */
    protected $tovutiService;

    public function __construct(TovutiServiceInterface $tovutiService)
    {
        $this->tovutiService = $tovutiService;
    }

    // public function indexAction()
    // {
    //     return new ViewModel(array(
    //         'posts' => $this->postService->findAllPosts()
    //     ));
    // }



    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 4, 5, 7))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    // public function onBootstrap($e)
    // {
    //     // some stuff
    //     $eventManager = $e->getApplication()->getEventManager();
    //     $eventManager->attach("dispatch", function($e) {
    //         echo "Dispatch!";
    //     });
    //     // some stuff
    // }

    // public function getUserTable()
    // {
    //     if (!$this->userTable) {
    //         $sm = $this->getServiceLocator();
    //         $this->userTable = $sm->get('Admin\Model\UserTable');
    //     }
    //     return $this->userTable;
    // }

    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }

    public function getTraininglogTable()
    {
        if (!isset($this->traininglogTable)) {
            $sm = $this->getServiceLocator();
            $this->traininglogTable = $sm->get('Traininglog\Model\TraininglogTable');
        }
        return $this->traininglogTable;
    }

    public function getTraininglogtypeTable()
    {
        if (!$this->traininglogtypeTable) {
            $sm = $this->getServiceLocator();
            $this->traininglogtypeTable = $sm->get('Traininglog\Model\TraininglogtypeTable');
        }
        return $this->traininglogtypeTable;
    }

    public function getRegulationTable()
    {
        if (!isset($this->regulationTable)) {
            $sm = $this->getServiceLocator();
            $this->regulationTable = $sm->get('Traininglog\Model\RegulationTable');
        }
        return $this->regulationTable;
    }

    public function getCompanyRolesTable()
    {
        if (!isset($this->companyRolesTable)) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
    }

    public function getCompanyTable()
    {
        if (!isset($this->companyTable)) {
            $sm = $this->getServiceLocator();
            $this->companyTable = $sm->get('Client\Model\CompanyTable');
        }
        return $this->companyTable;
    }

    public function getEmployeemasterlistTable()
    {
        if (!isset($this->employeemasterlistTable)) {
            $sm = $this->getServiceLocator();
            $this->employeemasterlistTable = $sm->get('Traininglog\Model\EmployeemasterlistTable');
        }
        return $this->employeemasterlistTable;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function employeemasterlistAction()
    {
        
        $cId = $this->params('company_id');
        
        $identity = $this->getIdentity();
        $isAdmin = $identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN ? true : false;
        $companyObject = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);

        if($isAdmin) {
            $listOfTovutiUserPerCompany = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllTovutiUser();
            
        } else {
            $listOfTovutiUserPerCompany = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllTovutiUsersByCompanyName($companyObject->c_name);
        }

        //getAllTovutiUser
        //$form = new EmployeemasterlistForm($this->getServiceLocator());
        $traineeUsers = [];
        foreach ($listOfTovutiUserPerCompany as $value) {
            $traineeUser = [];
            $traineeUser['Id'] = $value->tovuti_id;
            $traineeUser['Department'] = $value->department;
            $traineeUser['FirstName'] = $value->first_name;
            $traineeUser['LastName'] = $value->last_name;
            $traineeUser['Email'] = $value->email;
            $traineeUser['JobTitle'] = $value->job_title;
            $traineeUser['UserName'] = $value->user_name;
            $traineeUser['RegisteredDate'] = $value->register_date;
            array_push($traineeUsers, $traineeUser);
        }
        
        return array(
            //'lists' => $listOfTovutiUserPerCompany,
            //'form' => $form,
            'traineeUsers' => $traineeUsers,
            'paginationDetails' => count($traineeUsers),
        );
    }

    function editAction(){
        $identity = $this->getIdentity();
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        
        //$jobTitles1 = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllJobTitlesByCompanyName($clientObj->c_name);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $departments1 = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($this->params()->fromPost('companyname'));

            $post = $request->getPost();
            $tovuti_user_id = $this->params()->fromPost('tovutiuserid');
            $company_name = $this->params()->fromPost('companyname');
            $tovuti_id = $this->params()->fromPost('tovutiid');
            $firstname = $this->params()->fromPost('firstname');
            $lastname = $this->params()->fromPost('lastname');
            $email = $this->params()->fromPost('email');
            $jobtitle = $this->params()->fromPost('jobtitle');
            $department = $departments1[$this->params()->fromPost('department')];
            $username = $this->params()->fromPost('username');
            $departments = $this->params()->fromPost('departments');
            $requirereset = $this->params()->fromPost('requirereset');
            $status = $this->params()->fromPost('status');
            
            //Get Current User from Tovuti to know what ID is in the UserGroup array
            $currentTovutiUserResponse = $this->tovutiService->GetTovutiUser($tovuti_id);
            $currentTovutiUserObject = $this->tovutiService->SetTovutiUser($currentTovutiUserResponse);
            $currentTovutiUserDTOObject = $this->tovutiService->SetTovutiUserDTO($currentTovutiUserResponse);

            $fieldValues = [];
            
            $customFieldsUpdated = $this->tovutiService->UpdateTovutiCustomField($currentTovutiUserResponse['customFields'], array('field' => TovutiUser::CUSTOM_FIELD_FIRST_NAME, 'value' => $firstname));
            $customFieldsUpdated = $this->tovutiService->UpdateTovutiCustomField($customFieldsUpdated, array('field' => TovutiUser::CUSTOM_FIELD_LAST_NAME, 'value' => $lastname));
            $customFieldsUpdated = $this->tovutiService->UpdateTovutiCustomField($customFieldsUpdated, array('field' => TovutiUser::CUSTOM_FIELD_DEPARTMENT, 'value' => $department));
            $customFieldsUpdated = $this->tovutiService->UpdateTovutiCustomField($customFieldsUpdated, array('field' => TovutiUser::CUSTOM_FIELD_JOB_TITLE, 'value' => $jobtitle));

            $userGroups = isset($departments) ? [2, $departments] : [2];
            
            $data = array(
                'name' => "{$firstname} {$lastname}",
                //'username' => $username,
                //'email' => $email,
                'requireReset' => $requirereset,
                'status' => $status,
                'customFields' => $customFieldsUpdated,
                'userGroupIds' => $userGroups
            );

            $updateUserToTovuti = $this->tovutiService->updateUserToTovuti($data, $tovuti_id);
            $currentTovutiUserObjectToSaveToDatabase = $currentTovutiUserObject;
            $currentTovutiUserObjectToSaveToDatabase->tovuti_user_id = $tovuti_user_id;
            $currentTovutiUserObjectToSaveToDatabase->first_name = $firstname;
            $currentTovutiUserObjectToSaveToDatabase->last_name = $lastname;
            $currentTovutiUserObjectToSaveToDatabase->name = "{$firstname} {$lastname}";
            $currentTovutiUserObjectToSaveToDatabase->email = $email;
            $currentTovutiUserObjectToSaveToDatabase->job_title = $jobtitle;
            $currentTovutiUserObjectToSaveToDatabase->department = $department;
            $currentTovutiUserObjectToSaveToDatabase->user_name = $username;
            $currentTovutiUserObjectToSaveToDatabase->user_group_id = $departments;
            $currentTovutiUserObjectToSaveToDatabase->require_reset = (isset($requirereset)) ? $requirereset : false;
            $currentTovutiUserObjectToSaveToDatabase->status = $status;

            $saveTovutiUserGroupRecordResponse = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->saveTovutiUserRecord($currentTovutiUserObjectToSaveToDatabase);

            return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
        }        

        
        $tovutiUserId = trim($this->params()->fromQuery('tovuti_id'));
        
        $getTovutiUserResponse = $this->tovutiService->GetTovutiUser($tovutiUserId);
        if ($getTovutiUserResponse == null) {
            $viewParams = array(
                'form' => new TovutiUserForm($this->getServiceLocator(), $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($clientObj->c_name)),
            );
            
            return new ViewModel($viewParams);
        }

        $tovutiUser = $this->tovutiService->SetTovutiUser($getTovutiUserResponse);
        $tovutiDatabaseObject = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getTovutiUserFomDatabase($tovutiUser->tovuti_id);
        $tovutiUser->tovuti_user_id = $tovutiDatabaseObject->tovuti_user_id;
        $saveTovutiUserGroupRecordResponse = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->saveTovutiUserRecord($tovutiUser);

        //Re-pull database object to ensure accurate data since data was pulled from Tovuti and saved.
        $tovutiDatabaseObject = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getTovutiUserFomDatabase($tovutiUser->tovuti_id);

        if (isset($tovutiDatabaseObject->company_name)){
            $local_company_name = $tovutiDatabaseObject->company_name;
        } else {
            $local_company_name = $clientObj->c_name;
        }

        $departments1 = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($local_company_name);  
        
        $form = new TovutiUserForm($this->getServiceLocator(), $departments1);


        //TODO: Fill information in form and open form.
        $form->get('tovutiuserid')->setValue($tovutiDatabaseObject->tovuti_user_id);
        $form->get('tovutiid')->setValue($tovutiDatabaseObject->tovuti_id);
        $form->get('companyname')->setValue($tovutiDatabaseObject->company_name);
        $form->get('firstname')->setValue($tovutiDatabaseObject->first_name);
        $form->get('lastname')->setValue($tovutiDatabaseObject->last_name);        
        $form->get('email')->setValue($tovutiDatabaseObject->email);
        $form->get('department')->setValue(in_array($tovutiDatabaseObject->department, $departments1));
        $form->get('jobtitle')->setValue($tovutiDatabaseObject->job_title);
        $form->get('username')->setValue($tovutiDatabaseObject->user_name);
        $form->get('registerdate')->setValue($tovutiDatabaseObject->register_date);
        $form->get('lastvisitdate')->setValue($tovutiDatabaseObject->last_visit_date);
        $form->get('requirereset')->setValue(isset($tovutiDatabaseObject->require_reset) ? $tovutiDatabaseObject->require_reset : false);
        $form->get('status')->setValue($tovutiDatabaseObject->status);
        
        $companyDepartments = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserGroupTable')->getAllTovutiUserGroupsPerCompany($local_company_name);
        $tovutiUserGroups = $this->tovutiService->GetUserGroupsByCompanyName($companyDepartments);
        $tovutiUserGroupSelected = '';
        foreach ($tovutiUserGroups as $individualUserGroup) {
            if ($individualUserGroup->id == $tovutiDatabaseObject->user_group_id) {
                $tovutiUserGroupSelected = $individualUserGroup;
            }
        }

        $viewParams = array(
            'form' => $form,
            'tovutiDatabaseObject' => $tovutiDatabaseObject,
            'departments' => $tovutiUserGroups,
            'selectedDepartment' => $tovutiUserGroupSelected,
            'isAdmin' => $identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN ? true : false,
            'departments1' => $departments1
        );

        $viewModel = new ViewModel($viewParams);
        
        return $viewModel;
    }

    public function inactivatetovutiuserAction() {
        $disabledStatus = 'disabled';

        $tovutiUserId = trim($this->params()->fromQuery('tovuti_id'));
        $getTovutiUserResponse = $this->tovutiService->GetTovutiUser($tovutiUserId);
        
        $data = array(
            'name' => $getTovutiUserResponse['name'],
            'requireReset' => $getTovutiUserResponse['requireReset'],
            'status' => $disabledStatus,
            'customFields' => $getTovutiUserResponse['customFields'],
            'userGroupIds' => $getTovutiUserResponse['userGroupIds']
        );

        $updateUserToTovuti = $this->tovutiService->updateUserToTovuti($data, $tovutiUserId);
        $tovutiDatabaseObject = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getTovutiUserFomDatabase($tovutiUserId);
        $tovutiDatabaseObject->status = $disabledStatus;
        $saveTovutiUserGroupRecordResponse = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->saveTovutiUserRecord($tovutiDatabaseObject);
        
        return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
    }

    // public function addAction()
    //  {
    //      $form = new AlbumForm();
    //      $form->get('submit')->setValue('Add');

    //      $request = $this->getRequest();
    //      if ($request->isPost()) {
    //          $album = new Album();
    //          $form->setInputFilter($album->getInputFilter());
    //          $form->setData($request->getPost());

    //          if ($form->isValid()) {
    //              $album->exchangeArray($form->getData());
    //              $this->getAlbumTable()->saveAlbum($album);

    //              // Redirect to list of albums
    //              return $this->redirect()->toRoute('album');
    //          }
    //      }
    //      return array('form' => $form);
    //  }

    //public function addlitmosuserAction() {
    public function addAction() {
        $identity = $this->getIdentity();
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        $departments1 = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($clientObj->c_name);  
        $jobTitles1 = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllJobTitlesByCompanyName($clientObj->c_name);
        
        if(isset($_POST['cancel'])) {
            return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
        }

        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        $form = new TovutiUserForm($this->getServiceLocator(), $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getAllDepartmentsByCompanyName($clientObj->c_name));
        $form->get('submit')->setValue('Add');
        $identity = $this->getIdentity();
        

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $tovuti_user_id = $this->params()->fromPost('tovutiuserid');
            $company_name = $this->params()->fromPost('companyname');
            $tovuti_id = $this->params()->fromPost('tovutiid');
            $firstname = $this->params()->fromPost('firstname');
            $lastname = $this->params()->fromPost('lastname');
            $email = $this->params()->fromPost('email');
            $jobtitle = $this->params()->fromPost('jobtitle');
            //$jobtitle = $jobTitles1[$this->params()->fromPost('jobtitle')];
            //$department = $this->params()->fromPost('department');
            $department = $departments1[$this->params()->fromPost('department')];
            $username = $this->params()->fromPost('username');
            $departments = $this->params()->fromPost('departments');
            $requirereset = $this->params()->fromPost('requirereset');
            $status = $this->params()->fromPost('status');
            
            $userGroups = is_null($departments) ? [2] : [2, $departments];
            $customField = array(
                'companyname' => $clientObj->c_name,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'department' => $department,
                'jobtitle' => $jobtitle
            );
            $customFieldsResponse = $this->tovutiService->InsertTovutiCustomField($customField);
            $temp = [];
            $data = array(
                'name' => "{$firstname} {$lastname}",
                'password' => 'Training123',
                'username' => $username,
                'email' => $email,
                'requireReset' => 1,
                'status' => 'active', //$status,
                'customFields' => $customFieldsResponse,
                'userGroupIds' => $userGroups,
                'enrolledCourseIds' => array(),
                'accessLevels' => array(1, 2),
                'objects' => []
            );

            $addNewUserToTovuti = $this->tovutiService->addNewUserToTovuti($data);
            //TODO $addNewToTovuti could be error, should do something to handle this.
            //errors[0] = The username has already been taken.
            //errors[0] = The username field is required.
            //errors[0] = The email must be a valid email address.

            //TODO: Update Database
            //saveTovutiUserRecord(TovutiUser $tovuti_user)
            //$currentTovutiUserObjectToSaveToDatabase = $addNewUserToTovuti;
            $currentTovutiUserObjectToSaveToDatabase = new TovutiUser();
            $currentTovutiUserObjectToSaveToDatabase->tovuti_id = $addNewUserToTovuti['id'];
            $currentTovutiUserObjectToSaveToDatabase->first_name = $firstname;
            $currentTovutiUserObjectToSaveToDatabase->last_name = $lastname;
            $currentTovutiUserObjectToSaveToDatabase->company_name = $clientObj->c_name;
            $currentTovutiUserObjectToSaveToDatabase->department = $department;
            $currentTovutiUserObjectToSaveToDatabase->job_title = $jobtitle;
            $currentTovutiUserObjectToSaveToDatabase->name = "{$firstname} {$lastname}";
            $currentTovutiUserObjectToSaveToDatabase->user_name = $username;
            $currentTovutiUserObjectToSaveToDatabase->email = $email;
            $currentTovutiUserObjectToSaveToDatabase->user_group_id = $departments;
            $currentTovutiUserObjectToSaveToDatabase->register_date = $addNewUserToTovuti['registerDate'];
            $currentTovutiUserObjectToSaveToDatabase->last_visit_date = $addNewUserToTovuti['lastvisitDate'];
            $currentTovutiUserObjectToSaveToDatabase->require_reset = 1;
            $currentTovutiUserObjectToSaveToDatabase->status = 'active';

            $saveTovutiUserGroupRecordResponse = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->saveTovutiUserRecord($currentTovutiUserObjectToSaveToDatabase);
            //TODO if ID does not come back there was an error.

            return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
        }

        //TODO: Fill information in form and open form.
        //$form->get('tovutiuserid')->setValue($tovutiDatabaseObject->tovuti_user_id);
        //$form->get('tovutiid')->setValue($tovutiDatabaseObject->tovuti_id);
        $form->get('companyname')->setValue($clientObj->c_name);
        $form->get('firstname')->setValue("");//$tovutiDatabaseObject->first_name);
        $form->get('lastname')->setValue("");//$tovutiDatabaseObject->last_name);        
        $form->get('email')->setValue("");//$tovutiDatabaseObject->email);
        //$form->get('department')->setValue($tovutiDatabaseObject->department);
        $form->get('department')->setValue("");
        //$form->get('jobtitle')->setValue($tovutiDatabaseObject->job_title);
        $form->get('jobtitle')->setValue("");
        $form->get('username')->setValue("");//$tovutiDatabaseObject->user_name);
        $form->get('registerdate')->
        setValue("");//$tovutiDatabaseObject->register_date);
        $form->get('lastvisitdate')->setValue("");//$tovutiDatabaseObject->last_visit_date);
        $form->get('requirereset')->setValue(1);//$tovutiDatabaseObject->require_reset);
        $form->get('status')->setValue("active");//$tovutiDatabaseObject->status);
        // $form->get('litmosuserid')->setValue($queryStringLitmosUserId);

        //TODO Fix this 10-28-2023
        //$result = $this->GetLitmosUser($litmosUserId);      
        
        //$teamsResponse = $this->GetTeamsByCompany($clientObj->c_id);
        //$companyDepartments = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->getDepartmentsByCompany($clientObj->c_name);
        $companyDepartments = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserGroupTable')->getAllTovutiUserGroupsPerCompany($clientObj->c_name);
        $tovutiUserGroups = $this->tovutiService->GetUserGroupsByCompanyName($companyDepartments);
        $tovutiUserGroupSelected = new TovutiUserGroup();
        
        $viewParams = array(
            'form' => $form,
            // 'teams' => $teams,
            // 'secondaryTeamId' => $this->GetSecondaryTeamByUserId($litmosUserId, $litmosParentTeamId),
            //'tovutiDatabaseObject' => $tovutiDatabaseObject,
            'departments' => $tovutiUserGroups,
            'selectedDepartment' => $tovutiUserGroupSelected,//array('id' => 0, 'group_name' => ''),
            'isAdmin' => $identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN ? true : false,
        );

        $viewModel = new ViewModel($viewParams);
        
        return $viewModel;
    }

    

  function GetTeamList(){
    //https://api.tovuti.io/api/v1/user/1622116/
    $ch = curl_init();
    $url = "https://yourdomain.com/api/v1/users/create";
    $curl = curl_init($url);

    $headers = [
        //"Authorization: apikey ce06ab98-4c7a-407f-a8b8-e593f64a4365"
        'x-api-key: 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        'Content-Type: application/json',

    ];

        
    $url = "https://carosh.tovuti.io/api/v1/users/groups/89";
    $curl = curl_init($url);
    $header = array();
    $header[] = 'Content-type: application/json';
    $header[] = 'x-public-key: api_pk_FC733AF9059C48F0BEAD3D512A656485';
    $header[] = 'x-api-key: api_sk_DDD02AFFF4CA4D57AD71A1284456C7B7';
    //if you want to send filters in the body of the request
    $body = new stdClass();
    $body->user = 601;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_VERBOSE, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
  }



    function DoesUserExistInLitmos($username) {
        
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
    
        ];
    
        $url = 'https://api.litmos.com/v1.svc/users/' . $username . '?source=hipaasuite&format=json';

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $status_code === 404 ? false : true;        
        
    }







}