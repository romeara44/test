<?php

namespace Trainee\Controller;

use Trainee\Form\Add;
use Trainee\Entity\TraineePost;
use Trainee\Model\Album;
use Trainee\InputFilter\AddPost;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();

    }

    public function addAction()
    {
        //$test = $this->getServiceLocator();
        //$form = new Add('addpost');
        $form = new Add('addpost');
        
        

        if ($this->request->isPost()) {
            //$traineePost = new Post();
            //$form->bind($traineePost);

            $form->setInputFilter(new AddPost());
            $form->setData($this->request->getPost());
            
            //$form->bind($traineePost);

            $album = new Album();
            $album->exchangeArray($this->request->getPost());
            $test = $album->firstname;

            //$litmosGetService = $this->getServiceLocator()->get('Trainee\Service\ILitmosGetService');
            //$litmosGetService->save();

            //Populate the Entity
            //TODO Can do better using the bind method, it was not working when I tried it last.
            $postData = $this->request->getPost();
            $traineePost = new TraineePost();
            $traineePost->setFirstName($postData['firstname']);
            $traineePost->setLastName($postData['lastname']);
            $traineePost->setEmail($postData['email']);
            $traineePost->setJobTitle($postData['jobtitle']);
            $traineePost->setUserName($postData['username']);

            
            $litmosGetService = $this->getServiceLocator()->get('Trainee\Service\ILitmosGetService');
            $departments = $litmosGetService->getDepartmentsByCompany("Wapello County Iowa");

            if ($form->isValid()) {
                //$data = $form->getData();
                // to access the data you need to use the following - $data['input_name']
                /**
                 * @todo Save Trainee post
                 */
                $t = "stuff";

            }
            
        }

        return new ViewModel(array(
            'form' => $form,
        ));
    }

    public function addlitmosuserAction() {
        $identity = $this->getIdentity();
        
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        $litmosParentTeamId = '';
        $form = new AddLitmosUserForm($this->getServiceLocator());
        $teamsResponse = $this->GetTeamsByCompany($clientObj->c_id);
        $teams = [];
        foreach ($teamsResponse as $ar) {
            $t = array(
                'id' => $ar['Id'],
                'name' => $ar['Name'],
            );
            
            if (isset($ar['ParentTeamId'])) {
                array_push($teams, $t);
            }
            else {
                $litmosParentTeamId = $ar['Id'];
            }
            
        }

        $companyDepartments = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->getDepartmentsByCompany($clientObj->c_name);
        
        $request = $this->getRequest();

        if ($request->isPost()) {

            $blogService = $this->getServiceLocator()->get('Trainee\Service\ILitmosGetService');

            if (!$blogService->DoesUserExistInLitmos($this->params()->fromPost('username'))) {
            //if (!$this->DoesUserExistInLitmos($this->params()->fromPost('username'))) {
                $post = $request->getPost();

                $username = $this->params()->fromPost('username');
                $firstname = $this->params()->fromPost('firstname');
                $lastname = $this->params()->fromPost('lastname');
                $email = $this->params()->fromPost('email');
                $companyname = $clientObj->c_name;
                $secondaryTeamId = $this->params()->fromPost('litmosTeam');
                $jobTitle = $this->params()->fromPost('jobtitle');
                $department = $this->params()->fromPost('department');
                $createUserResponse = $this->CreateUser($username, $firstname, $lastname, $email, $companyname, $jobTitle, $department);
                $createdUserObj = json_decode($createUserResponse);
                $teamsRequest = array($litmosParentTeamId, $secondaryTeamId);
                $this->AddUserToTeams($createdUserObj->{'Id'}, $teamsRequest);

                return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
            }
        }

        $viewParams = array(
            'form' => $form,
            'teams' => $teams,
            'departments' => $companyDepartments,
            'selectedDepartment' => ''
        );
            
        $viewModel = new ViewModel($viewParams);
        
        return $viewModel;
    }

    public function addlitmosuser1Action() {
        $form = new AddLitmosUser1Form($this->getServiceLocator());


        return new ViewModel([
            'form' => $form,//$this->form,
        ]);
    }

    function editlitmosuserAction(){
        $identity = $this->getIdentity();
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $username = $this->params()->fromPost('username');
            $firstname = $this->params()->fromPost('firstname');
            $lastname = $this->params()->fromPost('lastname');
            $email = $this->params()->fromPost('email');
            $username = $this->params()->fromPost('username');
            $secondaryTeamId = $this->params()->fromPost('litmosTeam');
            $litmosUserId = $this->params()->fromPost('litmosuserid');
            $department = $this->params()->fromPost('departments');
            $jobtitle = $this->params()->fromPost('jobtitle');
            
            $assignedTeamsResponse = $this->GetAssignedTeamsByUserId($litmosUserId);
            $oldSecondaryTeamId = '';
            foreach ($assignedTeamsResponse as $item) {
                
                if ($item['Name'] != $clientObj->c_name) {
                    $oldSecondaryTeamId = $item['Id'];
                }
            }

            if ($secondaryTeamId != $oldSecondaryTeamId) {
                $teamsRequest = array($secondaryTeamId);

                if ($oldSecondaryTeamId !== "") {
                    $this->RemoveUserFromIndividualTeam($litmosUserId, $oldSecondaryTeamId);
                }
                
                $this->AddUserToTeams($litmosUserId, $teamsRequest);
            }
            
            $this->UpdateLitmosUser($litmosUserId, $firstname, $lastname, $email, $clientObj->c_name, $department, $jobtitle, $username);
            
            return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
        }

        $form = new EditLitmosUserForm($this->getServiceLocator());
        $queryStringLitmosUserId = trim($this->params()->fromQuery('litmosuserid'));
        $litmosUserId = $queryStringLitmosUserId;

        $result = $this->GetLitmosUser($litmosUserId);

        $form->get('department')->setValue($result['CustomField1']);
        $form->get('username')->setValue($result['UserName']);
        $form->get('firstname')->setValue($result['FirstName']);
        $form->get('lastname')->setValue($result['LastName']);        
        $form->get('email')->setValue($result['Email']);
        $form->get('jobtitle')->setValue($result['JobTitle']);
        $form->get('litmosuserid')->setValue($queryStringLitmosUserId);
        
        $teamsResponse = $this->GetTeamsByCompany($clientObj->c_id);
        $companyDepartments = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->getDepartmentsByCompany($clientObj->c_name);


        $teams = [];
        foreach ($teamsResponse as $ar) {
            $t = array(
                'id' => $ar['Id'],
                'name' => $ar['Name'],
            );
            
            if (isset($ar['ParentTeamId'])) {
                array_push($teams, $t);
            }
            else {
                $litmosParentTeamId = $ar['Id'];
            }
            
        }

        $viewParams = array(
            'form' => $form,
            'teams' => $teams,
            'secondaryTeamId' => $this->GetSecondaryTeamByUserId($litmosUserId, $litmosParentTeamId),
            'departments' => $companyDepartments,
            'selectedDepartment' => $result['CustomField1'],
        );

        $viewModel = new ViewModel($viewParams);
        
        return $viewModel;
    }

    public function inactivatelitmosuserAction() {
        
        $responseTest = $this->GetUserPaginationInformationByCompany($companyName);

        $litmosUserId = trim($this->params()->fromQuery('litmosuserid'));
        $results = $this->InactivateUserFromLitmos($litmosUserId);
        
        return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
    }

    public function employeemasterlistAction()
    {
        
        //get all users from Litmos

        $companyName = 'Carosh Compliance Solutions';
        
        $test = array(
            'litmos_id' => 'userid394',
            'user_name' => 'romeara',
            'first_name' => 'Robert',
            'last_name' => 'OMeara'

        );
        /**
         * @var \Traininglog\Service\ITraininglogService $traininglogService
         */
        //$traininglogService = $this->getServiceLocator()->get('Traininglog\Service\ITraininglogService')->save($test);
        //$traininglogService = $this->getServiceLocator()->get('Traininglog\Model\TrainingUserTestTable')->save($test);
        //$traininglogService->save();

        $cId = $this->params('company_id');
        
        $identity = $this->getIdentity();
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        
        if (!in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_ADMIN, \Admin\Model\User::ROLE_CLIENT,
            \Admin\Model\User::ROLE_SENIOR_CONSULTANT, \Admin\Model\User::ROLE_CONSULTANT))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        if ($identity['u_role_id'] == \Admin\Model\User::ROLE_CLIENT) {
            $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
            if($this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->getTrainingManagerCompaniesIds($identity['u_id'])) {
            } else {
                return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
            }            
        }

        $request = $this->getRequest(); 
        if ($request->isPost()) {
            $this->getEmployeemasterlistTable()->saveEmployeemasterlists($request->getPost());
            return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'employeemasterlist'));
        
        } else {
            $litmosUsers = [];
            $form = new EmployeemasterlistForm($this->getServiceLocator());
            $lists = $this->getEmployeemasterlistTable()->getEmployeemasterlists();
            
            $paginationDetails = $this->GetUserPaginationInformationByCompany($clientObj->c_name);
            //$litmosUsers = $this->GetUsersByCompany('Carosh', 0);
            $litmosUsers = $this->GetUsersByCompany($clientObj->c_name, $paginationDetails['Pagination']['TotalCount']);
            
        }

        return array(
            'lists' => $lists,
            'form' => $form,
            'litmosUsers' => $litmosUsers,
            'paginationDetails' => $paginationDetails['Pagination']['TotalCount'],
        );
    }
}