<?php
 // Filename: /module/TraineeUser/src/TraineeUser/Model/TraineeUserInterface.php
 namespace Trainee\Entity;

 interface TraineeUserInterface
 {
    /**
     * Will return the Company Name of the Litmos Trainee User
    *
    * @return string
    */
    public function getCompanyName();

    /**
     * Will return the Litmos User Id of the Litmos Trainee User
    *
    * @return string
    */
    public function getLitmosUserId();

    /**
     * Will return the User Name of the Litmos Trainee User
    *
    * @return string
    */
    public function getUserName();

    /**
     * Will return the First Name of the Litmos Trainee User
    *
    * @return string
    */
    public function getFirstName();

    /**
     * Will return the Last Name of the Litmos Trainee User
    *
    * @return string
    */
    public function getLastName();

    /**
     * Will return the Email of the Litmos Trainee User
    *
    * @return string
    */
    public function getEmail();

    /**
     * Will return the Department of the Litmos Trainee User
    *
    * @return string
    */
    public function getDepartment();

    /**
     * Will return the Job Title of the Litmos Trainee User
    *
    * @return string
    */
    public function getJobTitle();

 }