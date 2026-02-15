<?php

namespace Trainee\Service;

use Trainee\Entity\Post;

class LitmosPostService implements ILitmosPostService
{
    //protected $postRepository;
    protected $traineeRepository;

    
    /**
     * Add user to Litmos team
     * //DELETE /users/{userid}?
     */
    function RemoveUserFromIndividualTeam($userid, $teamid) {
        
        $ch = curl_init();
    
        $headers = [
            'APIKEY: ce06ab98-4c7a-407f-a8b8-e593f64a4365',
            'Content-Type: application/json',
        ];
        $url = 'https://api.litmos.com/v1.svc/teams/' . $teamid . '/users/' . $userid . '?source=hipaasuite';

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "DELETE",
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








    public function setTraineeRepository($traineeRepository)
    {
        $this->traineeRepository = $traineeRepository;
    }

    public function getTraineeRepository()
    {
        return $this->traineeRepository;
    }
}