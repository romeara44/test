<?php
namespace Breachlog\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Breachremediationplanaction
{
    const STATUS_TODO = 0;
    const STATUS_PENDING_APPROVAL = 10;
    const STATUS_COMPLETED = 20;

    public static $tasks = array(
        1 => 'HIPAA Privacy/Security Officer, after investigation, determines that all conditions are met for a breach, requiring following the breach notification rule reporting requirements',
        2 => 'File a police report if there was theft involved.',
        3 => 'Notify CEO/HIPAA committee of breach',
        4 => 'Analyze the information at risk and determine of other agencies need to be informed (i.e. grant programs or other entity funders)',
        5 => 'Contact your corporate attorney and let them know what happened and that the agency will be proceeding with OCR reporting requirements in the breach notification rule.',
        6 => 'Prepare a list of individuals that will need an individual notice sent to them, this includes gathering most current addresses, etc.',
        7 => 'Prepare individual written notice ensuring the elements required from notification rule are in the dialogue of the letter.  This must be sent by first class mail.  Email is an accepted form of written notice, as long as the individual has agreed to email communications.  The written notice must include a description of breach, type of information involved in the breach, steps the individual should take to protect themselves, what the entity is doing to investigate the breach, mitigate it and prevent further breaches, as well as contact information for the covered entity.  Keep in mind that if you get 10 or more returns for wrong addresses or out of date addresses, the notice must be posted on the organization website and/or published by a media outlet where the affected individuals are most likely to reside.',
        8 => 'Set up a phone line, or designate a phone line for people to call to inquire about the breach or ask if their information was compromised.  Please keep in mind the any possible language barriers of clients when creating the communications.',
        9 => 'Prepare a media release with marketing/business development for the appropriate media outlets in the affected area, this includes TV and radio.  It is up to the media stations to decide if they want to run it.  In addition, it is good to have a media communication plan if the story does run and the organization gets contacted for interviews, etc. This notification must include the same information required for individual notice.',
        10 => 'Notice to the Secretary – This is done through the HHS website, a form to fill out.',
        11 => 'Wait for communication from HHS who will most likely request additional documentation or have additional questions.',
        12 => 'When they are through with their investigation and findings, they will send you a letter notifying you of any areas in which you are not compliant, and any monetary penalties (fines) that are issued.',
    );

    public static $statusesNames = array('' => 'To-Do', self::STATUS_TODO => 'To-Do', self::STATUS_PENDING_APPROVAL => 'Pending Approval', self::STATUS_COMPLETED => 'Completed');

    public $brpa_id;
    public $brpa_brp_id;
    public $brpa_task;
    public $brpa_action_plan;
    public $brpa_status;
    public $brpa_contact_u_id;
    public $brpa_approver_u_id;
    public $brpa_target_date;
    public $brpa_active;
    public $brpa_create_date;
    public $brpa_latest_action_date;

    public $_contact_name;
    public $_approver_name;

    public $_brpa_target_date_formatted;
    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->brpa_id     = (isset($data['brpa_id'])) ? $data['brpa_id'] : null;
        $this->brpa_brp_id     = (isset($data['brpa_brp_id'])) ? $data['brpa_brp_id'] : null;
        $this->brpa_task     = (isset($data['brpa_task'])) ? $data['brpa_task'] : null;
        $this->brpa_action_plan     = (isset($data['brpa_action_plan'])) ? $data['brpa_action_plan'] : null;
        $this->brpa_status     = (isset($data['brpa_status'])) ? $data['brpa_status'] : null;
        $this->brpa_contact_u_id     = (isset($data['brpa_contact_u_id'])) ? $data['brpa_contact_u_id'] : null;
        $this->brpa_approver_u_id     = (isset($data['brpa_approver_u_id'])) ? $data['brpa_approver_u_id'] : null;
        $this->brpa_target_date     = (isset($data['brpa_target_date'])) ? $data['brpa_target_date'] : null;
        $this->brpa_active     = (isset($data['brpa_active'])) ? $data['brpa_active'] : null;
        $this->brpa_create_date     = (isset($data['brpa_create_date'])) ? $data['brpa_create_date'] : null;
        $this->brpa_latest_action_date     = (isset($data['brpa_latest_action_date'])) ? $data['brpa_latest_action_date'] : null;
        $this->_contact_name     = (isset($data['_contact_name'])) ? $data['_contact_name'] : null;
        $this->_approver_name     = (isset($data['_approver_name'])) ? $data['_approver_name'] : null;
        $this->_brpa_target_date_formatted     = (isset($data['_brpa_target_date_formatted'])) ? $data['_brpa_target_date_formatted'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter($sl, $isEdit = false)
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'brpa_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Digits'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'brpa_task',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),

            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'brpa_contact_u_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'brpa_status',
                'required' => false,
            )));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}