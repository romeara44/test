<?php

namespace Trainee\Service;

interface ILitmosPutService
{

    /**
     * Inactivate User From Litmos
     */
    public function InactivateUserFromLitmos($id);

    /**
     * Update Litmos User
     */
    public function UpdateLitmosUser($litmosUserId, $firstName, $lastName, $email, $companyName, $department, $jobtitle, $username);

    /**
     * Update Trainee User Master Table
     */
    public function UpdateTraineeUserMasterTable($companyName);

    
}