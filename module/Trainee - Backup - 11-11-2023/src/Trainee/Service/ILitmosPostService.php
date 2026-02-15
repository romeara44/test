<?php

namespace Trainee\Service;

interface ILitmosPostService
{
    
    /**
     * Add a user to a Litmos team
     */
    public function AddUserToTeams($userId, $teamsInput);

    /**
     * 
     */
    public function CreateUser($userName, $firstName, $lastName, $email, $companyName, $jobTitle, $department);
}