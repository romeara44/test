<?php
// Filename: /module/Trainee/src/Trainee/Model/TraineeUser.php
namespace Trainee\Entity;

class TraineeUser implements TraineeUserInterface
{
    /**
     * @var string
    */
    protected $companyname;

    /**
     * @var string
    */
    protected $litmosuserid;

    /**
     * @var string
    */
    protected $username;

    /**
     * @var string
    */
    protected $firstname;

    /**
     * @var string
    */
    protected $lastname;

    /**
     * @var string
    */
    protected $email;

    /**
     * @var string
    */
    protected $department;

    /**
     * @var string
    */
    protected $jobtitle;

    
    /**
     * {@inheritDoc}
    */
    public function getCompanyName()
    {
        return $this->companyname;
    }

    /**
     * @param string $companyname
    */
    public function setCompanyName($companyname)
    {
        $this->companyname = $companyname;
    }

    /**
     * {@inheritDoc}
    */
    public function getLitmosUserId()
    {
        return $this->litmosuserid;
    }

    /**
     * @param string $litmosuserid
    */
    public function setLitmosUserId($litmosuserid)
    {
        $this->litmosuserid = $litmosuserid;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
    
}