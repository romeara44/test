<?php

namespace Trainee\Service;

use Trainee\Entity\Post;

class LitmosPutService implements ILitmosPutService
{
    //protected $postRepository;
    protected $traineeRepository;

    /**
     * 
     */
    // public function save()
    // {
    //     $v= "stuff";
    //     $post = new Post();

    //     $this->postRepository->save($post);
    // }

    /**
     * 
     */
    // public function getDepartmentsByCompany($companyName)
    // {
    //     return $this->postRepository->getDepartmentsByCompany($companyName);
    // }

    /**
     * Inactivate User From Litmos
     */
    public function InactivateUserFromLitmos($id)
    {
        //$id = 'zrYLDZYFUZwaUa1KAJQ-AQ2';

        $userResults = $this->GetLitmosUser($id);

        $ch = curl_init();

        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
        
        $fields = array(
            'Id' => $userResults['Id'],
            'UserName' => $userResults['UserName'],
            'FirstName' => $userResults['FirstName'],
            'LastName' => $userResults['LastName'],
            'FullName' => $userResults['FullName'],
            'Email' => $userResults['Email'],
            'AccessLevel' => $userResults['AccessLevel'],
            'DisableMessages' => $userResults['DisableMessages'],
            'Active' => true,
            'Skype' => $userResults['Skype'],
            'PhoneWork' => $userResults['PhoneWork'],
            'PhoneMobile' => $userResults['PhoneMobile'],
            'LastLogin' => $userResults['LastLogin'],
            'IsCustomUsername' => $userResults['IsCustomUsername'],
            'Password' => $userResults['Password'],
            'SkipFirstLogin' => $userResults['SkipFirstLogin'],
            'TimeZone' => $userResults['TimeZone'],
            'SalesforceId' => $userResults['SalesforceId'],
            'OriginalId' => $userResults['OriginalId'],
            'Street1' => $userResults['Street1'],
            'Street2' => $userResults['Street2'],
            'City' => $userResults['City'],
            'State' => $userResults['State'],
            'PostalCode' => $userResults['PostalCode'],
            'Country' => $userResults['Country'],
            'CompanyName' => $userResults['CompanyName'],
            'JobTitle' => $userResults['JobTitle'],
            'CustomField1' => $userResults['CustomField1'],
            'CustomField2' => $userResults['CustomField2'],
            'CustomField3' => $userResults['CustomField3'],
            'CustomField4' => $userResults['CustomField4'],
            'CustomField5' => $userResults['CustomField5'],
            'CustomField6' => $userResults['CustomField6'],
            'CustomField7' => $userResults['CustomField7'],
            'CustomField8' => $userResults['CustomField8'],
            'CustomField9' => $userResults['CustomField9'],
            'CustomField10' => $userResults['CustomField10'],
            'Culture' => $userResults['Culture'],
            'SalesforceContactId' => $userResults['SalesforceContactId'],
            'SalesforceAccountId' => $userResults['SalesforceAccountId'],
            'CreatedDate' => $userResults['CreatedDate'],
            'Points' => $userResults['Points'],
            'Brand' => $userResults['Brand'],
            'ManagerId' => $userResults['ManagerId'],
            'ManagerName' => $userResults['ManagerName'],
            'EnableTextNotification' => $userResults['EnableTextNotification'],
            'Website' => $userResults['Website'],
            'Twitter' => $userResults['Twitter'],
            'ExpirationDate' => $userResults['ExpirationDate'],
            'JobRole' => $userResults['JobRole'],
            'ExternalEmployeeId' => $userResults['ExternalEmployeeId'],
            'ProfileType' => $userResults['ProfileType'],

        );

        $url = 'https://api.litmos.com/v1.svc/users/' . $id . '?source=hipaasuite';

        $decodedTest = json_encode($fields);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
        curl_close($ch);    
        
    }

    /**
     * Update Litmos User
     */
    function UpdateLitmosUser($litmosUserId, $firstName, $lastName, $email, $companyName, $department, $jobtitle, $username) {
        
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $userResults = $this->GetLitmosUser($litmosUserId);
        
        $fields = array(
            'Id' => $litmosUserId,
            'UserName' => $username,
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'FullName' => $firstName . ' ' . $lastName,
            'Email' => $email,
            'AccessLevel' => $userResults['AccessLevel'],
            'DisableMessages' => $userResults['DisableMessages'],
            'Active' => true,
            'Skype' => $userResults['Skype'],
            'PhoneWork' => $userResults['PhoneWork'],
            'PhoneMobile' => $userResults['PhoneMobile'],
            'LastLogin' => $userResults['LastLogin'],
            'IsCustomUsername' => $userResults['IsCustomUsername'],
            'Password' => $userResults['Password'],
            'SkipFirstLogin' => $userResults['SkipFirstLogin'],
            'TimeZone' => $userResults['TimeZone'],
            'SalesforceId' => $userResults['SalesforceId'],
            'OriginalId' => $userResults['OriginalId'],
            'Street1' => $userResults['Street1'],
            'Street2' => $userResults['Street2'],
            'City' => $userResults['City'],
            'State' => $userResults['State'],
            'PostalCode' => $userResults['PostalCode'],
            'Country' => $userResults['Country'],
            'CompanyName' => $companyName,
            'JobTitle' => $jobtitle,
            'CustomField1' => $department,
            'CustomField2' => $userResults['CustomField2'],
            'CustomField3' => $userResults['CustomField3'],
            'CustomField4' => $userResults['CustomField4'],
            'CustomField5' => $userResults['CustomField5'],
            'CustomField6' => $userResults['CustomField6'],
            'CustomField7' => $userResults['CustomField7'],
            'CustomField8' => $userResults['CustomField8'],
            'CustomField9' => $userResults['CustomField9'],
            'CustomField10' => $userResults['CustomField10'],
            'Culture' => $userResults['Culture'],
            'SalesforceContactId' => $userResults['SalesforceContactId'],
            'SalesforceAccountId' => $userResults['SalesforceAccountId'],
            'CreatedDate' => $userResults['CreatedDate'],
            'Points' => $userResults['Points'],
            'Brand' => $userResults['Brand'],
            'ManagerId' => $userResults['ManagerId'],
            'ManagerName' => $userResults['ManagerName'],
            'EnableTextNotification' => $userResults['EnableTextNotification'],
            'Website' => $userResults['Website'],
            'Twitter' => $userResults['Twitter'],
            'ExpirationDate' => $userResults['ExpirationDate'],
            'JobRole' => $userResults['JobRole'],
            'ExternalEmployeeId' => $userResults['ExternalEmployeeId'],
            'ProfileType' => $userResults['ProfileType'],

        );

        $url = 'https://api.litmos.com/v1.svc/users/' . $litmosUserId . '?source=hipaasuite';

        $decodedTest = json_encode($fields);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $clientObj = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->saveTraineeUserRecordArray($fields);
    }

    /**
     * Update Trainee User Master Table
     */
    function UpdateTraineeUserMasterTable($companyName){
        
        //Delete TrainUsers prior to reloading 
        $t3 = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->deleteTraineeUsersByCompany($companyName);
        //Department $t4 = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->getDepartmentsByCompany($companyName);
        $t1 = $this->GetUserPaginationInformationByCompany($companyName);
        $t2 = $this->GetUsersByCompany1($companyName, $t1['Pagination']['TotalCount']);


        foreach ($t2 as $item) {    
            $trainee_user = new TraineeUser();
            $trainee_user->litmos_id = $item['Id'];
            $trainee_user->user_name = $item['UserName'];
            $trainee_user->first_name =$item['FirstName'];
            $trainee_user->last_name = $item['LastName'];
            $trainee_user->full_name = $item['FullName'];
            $trainee_user->email = $item['Email'];
            $trainee_user->access_level = $item['AccessLevel'];
            $trainee_user->disable_messages = $item['DisableMessages'];
            $trainee_user->active = $item['Active'];
            $trainee_user->skype = $item['Skype'];
            $trainee_user->phone_work = $item['PhoneWork'];
            $trainee_user->phone_mobile = $item['PhoneMobile'];
            $trainee_user->last_login = $item['LastLogin'];
            $trainee_user->login_key = $item['LoginKey'];
            $trainee_user->is_custom_user_name = $item['IsCustomUsername'];
            $trainee_user->password = $item['Password'];
            $trainee_user->skip_first_login = $item['SkipFirstLogin'];
            $trainee_user->time_zone = $item['TimeZone'];
            $trainee_user->sales_force_id = $item['SalesforceId'];
            $trainee_user->original_id = $item['OriginalId'];
            $trainee_user->street_1 = $item['Street1'];
            $trainee_user->street_2 = $item['Street2'];
            $trainee_user->city = $item['City'];
            $trainee_user->state = $item['State'];
            $trainee_user->postal_code = $item['PostalCode'];
            $trainee_user->country = $item['Country'];
            $trainee_user->company_name = $item['CompanyName'];
            $trainee_user->job_title = $item['JobTitle'];
            $trainee_user->custom_field_1 = $item['CustomField1'];
            $trainee_user->custom_field_2 = $item['CustomField2'];
            $trainee_user->custom_field_3 = $item['CustomField3'];
            $trainee_user->custom_field_4 = $item['CustomField4'];
            $trainee_user->custom_field_5 = $item['CustomField5'];
            $trainee_user->custom_field_6 = $item['CustomField6'];
            $trainee_user->custom_field_7 = $item['CustomField7'];
            $trainee_user->custom_field_8 = $item['CustomField8'];
            $trainee_user->custom_field_9 = $item['CustomField9'];
            $trainee_user->custom_field_10 = $item['CustomField10'];
            $trainee_user->culture = $item['Culture'];
            $trainee_user->salesforce_contact_id = $item['SalesforceContactId'];
            $trainee_user->salesforce_account_id = $item['SalesforceAccountId'];
            $trainee_user->created_date = $item['CreatedDate'];
            $trainee_user->points = $item['Points'];
            $trainee_user->brand = $item['Brand'];
            $trainee_user->manager_id = $item['ManagerId'];
            $trainee_user->manager_name = $item['ManagerName'];
            $trainee_user->enable_text_notification = $item['EnableTextNotification'];
            $trainee_user->website = $item['Website'];
            $trainee_user->twitter = $item['Twitter'];
            $trainee_user->expiration_date = $item['ExpirationDate'];
            $trainee_user->job_role = $item['JobRole'];
            $trainee_user->external_employee_id = $item['ExternalEmployeeId'];
            $trainee_user->profile_type = $item['ProfileType'];    
    // protected $creation_date;
    // protected $modified_date;
            $clientObj = $this->getServiceLocator()->get('Traininglog\Model\TraineeUserTable')->saveTraineeUserRecord($trainee_user);
        }
    }



    public function setTraineeRepository($traineeRepository)
    {
        $this->traineeRepository = $traineeRepository;
    }

    public function getTraineeRepository()
    {
        return $this->traineeRepository;
    }
}