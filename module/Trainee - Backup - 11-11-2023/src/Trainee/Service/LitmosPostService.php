<?php

namespace Trainee\Service;

use Trainee\Entity\Post;

class LitmosPostService implements ILitmosPostService
{
    //protected $postRepository;
    protected $traineeRepository;

    

    /**
     * Add user to Litmos team
     */
    function AddUserToTeams($userId, $teamsInput) {

        $teams = [];

        foreach ($teamsInput as $item) {
            $team = array('Id' => $item);
            array_push($teams, $team);
        }
        
        $request = json_encode($teams);

        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
        
        $url = 'https://api.litmos.com/v1.svc/users/' . $userId . '/teams?source=hipaasuite&format=json';

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request,//json_encode($request),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_TIMEOUT => 30,
            //CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);    
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
        curl_close($ch);
    }

    /**
     * Create user
     */
    function CreateUser($userName, $firstName, $lastName, $email, $companyName, $jobTitle, $department) {

        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
    
        $fields = array(
            'UserName' => $userName, 
            'FirstName' => $firstName, 
            'LastName' => $lastName, 
            'FullName' => $firstName . ' ' . $lastName, 
            'Email' => $email,
            'JobTitle' => $jobTitle,
            'CustomField1' => $department,
            'AccessLevel' => 'Learner', 
            'DisableMessages' => false, 
            'CompanyName' => $companyName,
            'JobTitle' => $jobTitle,
            'CustomField1' => $department,
            'Active' => true,
            'LastLogin' => '',
            'LoginKey' => '',
            'SkipFirstLogin' => true,
            'TimeZone' => 'Central Standard Time'
        );

        $url = "https://api.litmos.com/v1.svc/users?source=hipaasuit&format=json&sendmessage=true";

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
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

        return $response;
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