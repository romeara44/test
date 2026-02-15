<?php

namespace Trainee\Service;

interface ILitmosGetService
{
    

    /**
     * 
     */
    //public function save();

    //public function getDepartmentsByCompany($companyName);

    

    /**
     * Get Assigned Teams By User Id
     */
    public function GetAssignedTeamsByUserId($userid);

    /**
     * Does User Exist In Litmos
     */
    public function DoesUserExistInLitmos($username);

    /**
     * Get Secondary Team By User Id - Another name for secondary team is Department
     */
    public function GetSecondaryTeamByUserId($userid, $litmosParentTeamId);

    /**
     * Get Teams By Company
     */
    public function GetTeamsByCompany($id);

    /**
     * Get user by company
     */
    public function GetUsersByCompany($companyName, $totalCount);

    /**
     * Get user by company 1
     */
    public function GetUsersByCompany1($companyName, $totalCount);

    /**
     * Get litmos user
     */
    public function GetLitmosUser($litmosUserId);

    /**
     * Get Litmos User by User Name
     */
    public function GetLitmosUserByUserName($username);

    /**
     * Get user pagination information by company
     */
    public function GetUserPaginationInformationByCompany($companyName);

}