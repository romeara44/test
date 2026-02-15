<?php
// module/Album/src/Album/Model/Album.php:
namespace Trainee\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Album implements InputFilterAwareInterface
{
    public $department;

    /**
     * @var string
     */
    public $firstname;

    public $lastname;
    public $email;
    public $jobtitle;
    public $username;
    public $traineetype;

    public function exchangeArray($data)
    {
        
        $this->department = (isset($data['department'])) ? $data['department'] : null;
        $this->firstname = (isset($data['firstname'])) ? $data['firstname'] : null;
        $this->lastname = (isset($data['lastname'])) ? $data['lastname'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->jobtitle = (isset($data['jobtitle'])) ? $data['jobtitle'] : null;
        $this->username = (isset($data['username'])) ? $data['username'] : null;
        $this->traineetype = (isset($data['traineetype'])) ? $data['traineetype'] : null;
    }

    // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        throw new \Exception("Not used");
    }
}