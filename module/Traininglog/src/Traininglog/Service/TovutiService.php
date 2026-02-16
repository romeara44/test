<?php
 // Filename: /module/Traininglog/src/Traininglog/Service/TovutiService.php

 namespace Traininglog\Service;

 use Traininglog\Model\TovutiUser;
 use Traininglog\Model\TovutiUserGroup;

 class TovutiService implements TovutiServiceInterface
 {
    public $TOVUTI_HEADERS = [
                'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
                'Content-Type: application/json'
            ];

    /**
      * {@inheritDoc}
    */
    function GetUserGroupFromTovuti($tovutiUserGroupId) {
        $ch = curl_init();
        $url = "https://api.tovuti.io/api/v1/userGroup/{$tovutiUserGroupId}/";
        $curl = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
      * {@inheritDoc}
    */
    // function GetAllUserGroupsFromTovuti() {
    //     $ch = curl_init();
    //     //$url = "https://api.tovuti.io/api/v1/userGroup/{$tovutiUserGroupId}/";
    //     $url = "https://api.tovuti.io/api/v1/userGroups?page=1&pageSize=100";
    //     $curl = curl_init($url);

    //     // $headers = [
    //     //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
    //     //     'Content-Type: application/json'
    //     // ];
        
    //     curl_setopt_array($ch, [
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_SSL_VERIFYPEER => false,
    //         CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS //$headers
    //     ]);
        
    //     $response = curl_exec($ch);
    //     $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);
    //     $result = json_decode($response, true);
        
    //     return $result;
    // }

    function GetAllUserGroupsFromTovuti() {
        //TODO: Add code to handle infinite pages.  So far this only handles one page of data.
        //'https://api.tovuti.io/api/v1/userGroups?page=1&pageSize=100'
        $ch = curl_init();
        //$url = "https://api.tovuti.io/api/v1/user/{$tovutiId}/";
        $url = "https://api.tovuti.io/api/v1/userGroups?page=1&pageSize=100";
        $curl = curl_init($url);

        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json'
        // ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;

    }

    function SetTovutiUserGroup($individualTovutiUserGroup){
        $tovutiUserGroup = new TovutiUserGroup();
        $tovutiUserGroup->tovuti_user_group_id = $individualTovutiUserGroup['tovuti_user_group_id'];
        $tovutiUserGroup->id = $individualTovutiUserGroup['id'];
        $tovutiUserGroup->parent_id = $individualTovutiUserGroup['parent_id'];
        $tovutiUserGroup->lft = $individualTovutiUserGroup['lft'];
        $tovutiUserGroup->rgt = $individualTovutiUserGroup['rgt'];
        $tovutiUserGroup->title = $individualTovutiUserGroup['title'];
        
        $posDash = stripos($individualTovutiUserGroup['title'], '-');
        $companyName = trim(substr($individualTovutiUserGroup['title'], 0, $posDash));
        $tovutiUserGroup->company_name = $companyName;

        $titleStringLength = strlen($individualTovutiUserGroup['title']);
        $groupName = trim(substr($individualTovutiUserGroup['title'], $posDash + 1, $titleStringLength));
        $tovutiUserGroup->group_name = $groupName;
        
        return $tovutiUserGroup;
    }

    /**
      * {@inheritDoc}
    */
    function GetTovutiUser($tovutiId) {
        
        $ch = curl_init();
        $url = "https://api.tovuti.io/api/v1/user/{$tovutiId}/";
        $curl = curl_init($url);

        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json'
        // ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
      * {@inheritDoc}
    */
    function ParseTovutiCustomField($customFields, $field){
        foreach ($customFields as $individualField) {
            if ($individualField['field_id'] == $field){
                return $individualField['value'];
            }
        }
    }

    /**
      * {@inheritDoc}
    */
    function UpdateTovutiCustomField(&$customFields, $fieldValue){
        $customFields3 = $customFields;

        $customFields1 = [];

        foreach ($customFields3 as $individualField) {
            if ($individualField['field_id'] == $fieldValue['field']){
                array_push($customFields1, array('field_id' => $fieldValue['field'], 'value' => $fieldValue['value'], 'title' => $individualField['title'], 'type' => $individualField['type']));

            } else {
                array_push($customFields1, array('field_id' => $individualField['field_id'], 'value' => $individualField['value'], 'title' => $individualField['title'], 'type' => $individualField['type']));

            }
            
        }

        return $customFields1;

    }

    /**
      * {@inheritDoc}
    */
    function InsertTovutiCustomField($customField){
        $text = 'text';

        $customFields = [];
        
        array_push($customFields, array('field_id' => 22, 'value' => $customField['companyname'], 'title' => 'Company Name', 'type' => $text));
        array_push($customFields, array('field_id' => 25, 'value' => $customField['jobtitle'], 'title' => 'Job Title', 'type' => $text));
        array_push($customFields, array('field_id' => 24, 'value' => $customField['department'], 'title' => 'Department', 'type' => $text));
        array_push($customFields, array('field_id' => 20, 'value' => $customField['lastname'], 'title' => 'Last Name', 'type' => $text));
        array_push($customFields, array('field_id' => 19, 'value' => $customField['firstname'], 'title' => 'First Name', 'type' => $text));
        
        return $customFields;

    }

    /**
      * {@inheritDoc}
    */
    function GetUserGroupsByCompanyName($userGroupsByCompany){
        $userGroups = [];

        foreach ($userGroupsByCompany as $individualGroup) {
            $tovuti_user_group = new TovutiUserGroup();
            $tovuti_user_group->tovuti_user_group_id = $individualGroup->tovuti_user_group_id;
            $tovuti_user_group->id = $individualGroup->id;
            $tovuti_user_group->parent_id = $individualGroup->lft;
            $tovuti_user_group->rgt = $individualGroup->rgt;
            $tovuti_user_group->title = $individualGroup->title;
            $tovuti_user_group->company_name = $individualGroup->company_name;
            $tovuti_user_group->group_name = $individualGroup->group_name;
            array_push($userGroups, $tovuti_user_group);
        }

        return $userGroups;
    }


    /**
      * {@inheritDoc}
    */
    function SetTovutiUser($item){
        
        $tovuti_user = new TovutiUser();
        //$tovuti_user->tovuti_user_id = $item['']
        $tovuti_user->tovuti_id = $item['id'];
        $tovuti_user->first_name = $this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_FIRST_NAME); //$this->ParseTovutiCustomField($item['customFields'], 19);
        $tovuti_user->last_name = $this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_LAST_NAME); //$this->ParseTovutiCustomField($item['customFields'], 20);
        $tovuti_user->company_name = $this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_COMPANY_NAME); //$this->ParseTovutiCustomField($item['customFields'], 22);
        $tovuti_user->department = $this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_DEPARTMENT); //$this->ParseTovutiCustomField($item['customFields'], 24);
        $tovuti_user->job_title = $this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_JOB_TITLE); //$this->ParseTovutiCustomField($item['customFields'], 25);
        $tovuti_user->name = $item['name'];
        $tovuti_user->user_name = $item['username'];
        $tovuti_user->email = $item['email'];
        $tovuti_user->user_group_id = $this->ParseUserGroupd($item['userGroupIds']); //$item['userGroupIds'];
        $tovuti_user->register_date = $item['registerDate'];
        $tovuti_user->last_visit_date = $item['lastvisitDate'];
        $tovuti_user->require_reset = $item['requireReset'];
        $tovuti_user->status = $item['status'];

        return $tovuti_user;
    }

    /**
      * {@inheritDoc}
    */
    function SetTovutiUserDTO($item){
        
        $tovuti_user = new TovutiUser();
        
        $tovuti_user->tovuti_id = $item['id'];
        $tovuti_user->name = "{$this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_FIRST_NAME)} {$this->ParseTovutiCustomField($item['customFields'], TovutiUser::CUSTOM_FIELD_LAST_NAME)}";
        $tovuti_user->user_name = $item['username'];
        $tovuti_user->email = $item['email'];
        $tovuti_user->name = $item['name'];
        $tovuti_user->register_date = $item['registerDate'];
        $tovuti_user->last_visit_date = $item['lastvisitDate'];
        $tovuti_user->require_reset = $item['requireReset'];
        $tovuti_user->status = $item['status'];        
        $tovuti_user->user_group_id = $this->ParseUserGroupd($item['userGroupIds']); //$item['userGroupIds'];
        $tovuti_user->userTeamIds = $this->ParseUserGroupd($item['userTeamIds']); //$item['userGroupIds'];
        $tovuti_user->customFields = $item['customFields'];
        $tovuti_user->enrolledCourseIds = $item['enrolledCourseIds'];
        $tovuti_user->accessLevels = $item['accessLevels'];
        $tovuti_user->objects = $item['objects'];

        return $tovuti_user;
    }

    /**
      * {@inheritDoc}
    */
    function ParseUserGroupd($userGroups){
        foreach ($userGroups as $individualGroup) {           
            if(!in_array($individualGroup, array(TovutiUserGroup::USER_GROUP_PUBLIC, TovutiUserGroup::USER_GROUP_REGISTERD, TovutiUserGroup::USER_GROUP_SUB_ADMINISTRATOR, TovutiUserGroup::USER_GROUP_SITE_ADMINISTRATOR, TovutiUserGroup::USER_GROUP_TEAM_ADMINSTRATOR))) {
                return $individualGroup;
            }

        }
    }

    /**
      * {@inheritDoc}
    */
    function RefreshAllTovutiUsersInDatabase($userList){
        foreach ($userList as $individualRecord) {
        
            sleep(1.5);
            $individualTovutiUser = $this->GetTovutiUser($individualRecord->tovuti_id);
            $errorResponse = "User id = {$individualRecord->tovuti_id} not found";
            
            if ($individualTovutiUser['error'] != $errorResponse) {
                $g = $this->SetTovutiUser($individualTovutiUser);
                $tovutiObj = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->getTovutiUserFomDatabase($individualRecord->tovuti_id);
                $g->tovuti_user_id = $tovutiObj->tovuti_user_id;
                //TODO: This currently does not save to the Database.
                $saveTovutiUserGroupRecordResponse = $this->getServiceLocator()->get('Traininglog\Model\TovutiUserTable')->saveTovutiUserRecord($g);
            }
            
        }
    }

    /**
      * {@inheritDoc}
    */
    public function getCustomFields() {
        $ch = curl_init();
    
        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json',
        // ];
    
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.tovuti.io/api/v1/customFields?page=1&pageSize=50",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS  //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;

    }

    /**
      * {@inheritDoc}
    */
    public function addNewUserToTovuti($data) {
        $ch = curl_init();
    
        $request = json_encode($data);

        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json',
        // ];
    
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.tovuti.io/api/v1/user",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request,//json_encode($request),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_TIMEOUT => 30,
            //CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS  //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;

    }
    
    /**
      * {@inheritDoc}
    */
    public function updateUserToTovuti($data, $tovutiUserId) {
        $ch = curl_init();
    
        $request = json_encode($data);

        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json',
        // ];
    
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.tovuti.io/api/v1/user/{$tovutiUserId}/",
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS  //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
      * {@inheritDoc}
    */
    public function insertUserToTovuti($data) {
        $ch = curl_init();
    
        $request = json_encode($data);

        // $headers = [
        //     'Authorization: Bearer 520|zBnGj3EFTLDoHoMeF1e1Zmtm9q0XulrVVBiyUD6D',
        //     'Content-Type: application/json',
        // ];
    
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.tovuti.io/api/v1/user",
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->TOVUTI_HEADERS  //$headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }
    
}