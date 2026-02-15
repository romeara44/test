<?php

namespace Trainee\Entity;

class TraineePost
{
    protected $department;

    /**
     * @var string
     */
    protected $firstname;

    protected $lastname;
    protected $email;
    protected $jobtitle;
    protected $username;
    protected $traineetype;


    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }
    
    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstName($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastName($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobtitle;
    }

    /**
     * @param string $jobtitle
     */
    public function setJobTitle($jobtitle)
    {
        $this->jobtitle = $jobtitle;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUserName($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getTraineeType()
    {
        return $this->traineetype;
    }

    /**
     * @param string $traineetype
     */
    public function setTraineeType($traineetype)
    {
        $this->traineetype = $traineetype;
    }

    
}