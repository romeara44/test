<?php
// Filename: /module/Traininglog/src/Traininglog/Entity/TovutiUserEntity.php
namespace Traininglog\Entity;

class TovutiUser implements TovutiUserEntityInterface
{
    /**
     * @var string
    */
    protected $tovuti_user_id;

    /**
     * @var string
    */
    protected $tovuti_id;

    /**
     * @var string
    */
    protected $first_name;

    /**
     * @var string
    */
    protected $last_name;

    /**
     * @var string
    */
    protected $company_name;

    /**
     * @var string
    */
    protected $department;

    /**
     * @var string
    */
    protected $job_title;

    /**
     * @var string
    */
    protected $name;

    /**
     * @var string
    */
    protected $user_name;

    /**
     * @var string
    */
    protected $email;

    /**
     * @var string
    */
    protected $groups;

    /**
     * @var string
    */
    protected $register_date;

    /**
     * @var string
    */
    protected $last_visit_date;

    /**
     * @var string
    */
    protected $require_reset;

    /**
     * @var string
    */
    protected $status;


    /**
     * {@inheritDoc}
    */
    public function getTovutiUserId()
    {
        return $this->tovutiuserid;
    }

    /**
     * @param string $tovutiuserid
    */
    public function setTovutiUserId($tovutiuserid)
    {
        $this->tovutiuserid = $tovutiuserid;
    }

    /**
     * {@inheritDoc}
    */
    public function getTovutiId()
    {
        return $this->tovutiid;
    }

    /**
     * @param string $tovutiid
    */
    public function setTovutiId($tovutiid)
    {
        $this->tovutiid = $tovutiid;
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
    public function getCompanyName()
    {
        return $this->companyname;
    }

    /**
     * @param string $companyname
    */
    public function settCompanyName($companyname)
    {
        $this->companyname = $companyname;
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

    /**
     * {@inheritDoc}
    */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
    */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
    */
    public function getUsertName()
    {
        return $this->username;
    }

    /**
     * @param string $username
    */
    public function setUsertName($username)
    {
        $this->username = $username;
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
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string $groups
    */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritDoc}
    */
    public function getGroups()
    {
        return $this->registerdate;
    }

    /**
     * @param string $registerdate
    */
    public function setGroups($registerdate)
    {
        $this->registerdate = $registerdate;
    }

    /**
     * {@inheritDoc}
    */
    public function getLastVisitDate()
    {
        return $this->lastvisitdate;
    }

    /**
     * @param string $lastvisitdate
    */
    public function setLastVisitDate($lastvisitdate)
    {
        $this->lastvisitdate = $lastvisitdate;
    }

    /**
     * {@inheritDoc}
    */
    public function getRequireReset()
    {
        return $this->requirereset;
    }

    /**
     * @param string $requirereset
    */
    public function setRequireReset($requirereset)
    {
        $this->requirereset = $requirereset;
    }

    /**
     * {@inheritDoc}
    */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
    */
    public function setStatus($status)
    {
        $this->status = $status;
    }

}