<?php

namespace Trainee\Service;

use Trainee\Entity\Post;

class LitmosGetService implements ILitmosGetService
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
     * Get Secondary Team By User Id
     */
    function GetSecondaryTeamByUserId($userid, $litmosParentTeamId){
        $results = $this->GetAssignedTeamsByUserId($userid);
        $secondaryTeamId = '';

        foreach ($results as $ar) {
            $t = array(
                'id' => $ar['Id'],
                'name' => $ar['Name'],
            );
            
            if ($ar['Id'] != $litmosParentTeamId) {
                $secondaryTeamId = $ar['Id'];
            }
        }

        return $secondaryTeamId;
    }    

    /**
     * Get Assigned Teams By User Id
     */
    function GetAssignedTeamsByUserId($userid){
        $results = [];
    
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $url = "https://api.litmos.com/v1.svc/users/{$userid}/teams?source=hipaasuite&format=json";

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
     * Check if a user exists in Litmos
     */
    public function DoesUserExistInLitmos($username){
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

    /**
     * Get litmos user
     */
    function GetLitmosUser($litmosUserId) {
        $results = [];
    
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $url = "https://api.litmos.com/v1.svc/users/{$litmosUserId}?source=hipaasuite&format=json";

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    
    /**
     * Get Litmos User By User Name
     * //GET /users/{username}?
     */
    function GetLitmosUserByUserName($username) {
        
        $results = [];
    
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $url = "https://api.litmos.com/v1.svc/users/{$litmosUserId}?source=hipaasuite&format=json";
        $url1 = "https://api.litmos.com/v1.svc/users/' . $litmosUserId . '?source=hipaasuite&format=json";

        curl_setopt_array($ch, [
            CURLOPT_URL => $url1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
     * Get teams by company
     */
    function GetTeamsByCompany($id){
        $clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($id);
        $companyName = urlencode($clientObj->c_name);
        
        $results = [];
    
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $url = "https://api.litmos.com/v1.svc/teams?source=hipaasuite&format=json&search={$companyName}";

        curl_setopt_array($ch, [
            CURLOPT_URL => @url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);
        
        return $result;
    }

    /**
     * Get user by company
     */
    function GetUsersByCompany($companyName, $totalCount) {
        $company = urlencode($companyName);
        $trips = intval($totalCount / 100) + 1;

        $results = [];
        $users = [];

        for ($x = 0; $x < $trips; $x++) {
            $ch = curl_init();
            $trip = $x * 100;
            $headers = [
                'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
                'Content-Type: application/json',
        
            ];
        
            $url = 'https://api.litmos.com/v1.svc/users/details?source=hipaasuite&format=json&showInactive=false&start=' . $trip . '&limit=100&search=' . $company;

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => $headers
            ]);
            
            $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = json_decode($response, true);
            
            
            foreach ($result as $item) {          
                $fields = array(
                    'Id' => $item['Id'],
                    'CustomField1' => $item['CustomField1'], 
                    'FirstName' => $item['FirstName'], 
                    'LastName' => $item['LastName'],
                    'Email' => $item['Email'],
                    'Active' => $item['Active'],
                    'JobTitle' => $item['JobTitle'], 
                    'UserName' => $item['UserName'], 
                    'TraineeType' => $item['UserName'],
                    'CreatedDate' => $item['CreatedDate'],
                    'InactiveDate' => $item['CreatedDate']
                );

                array_push($users, $fields);
            }
        }

        return $users;
    }

    /**
     * Get User by company 1
     */
    function GetUsersByCompany1($companyName, $totalCount) {
        $company = urlencode($companyName);
        $trips = intval($totalCount / 100) + 1;

        $results = [];
        $users = array();

        for ($x = 0; $x < $trips; $x++) {
            $ch = curl_init();
            $trip = $x * 100;
            $headers = [
                'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
                'Content-Type: application/json',
            ];
        
            $url = 'https://api.litmos.com/v1.svc/users/details?source=hipaasuite&format=json&start=' . $trip . '&limit=100&search=' . $company;

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => $headers
            ]);
            
            $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = json_decode($response, true);
            array_push($users, ...$result);
            
        }

        return $users;
    }

    /**
     * Get user pagination information by company
     */
    function GetUserPaginationInformationByCompany($companyName) {
        $company = urlencode($companyName);
        $results = [];
    
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $url = 'https://api.litmos.com/v1.svc/users/paginated?source=hipaasuite&format=json&limit=1&search=' . $company;

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
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