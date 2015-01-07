<?php
namespace Assessment\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Remediationplanaction
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
    public static $levelsNames = array('' => '', 0 => 'Low', 1 => 'Medium', 2 => 'High', 3 => 'N/A');

    public $rpa_id;
    public $rpa_rp_id;
    public $rpa_threat;
    public $rpa_action_plan;
    public $rpa_policy;
    public $rpa_adr_id;
    public $rpa_risk_score;
    public $rpa_risk_level;
    public $_rpa_risk_level_sort;
    public $rpa_status;
    public $rpa_contact_u_id;
    public $rpa_approver_u_id;
    public $rpa_target_date;
    public $rpa_active;
    public $rpa_create_date;
    public $rpa_aqc_id;

    public $_contact_name;
    public $_approver_name;

    public $_location_name;
    public $_rpa_target_date_formatted;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->rpa_id     = (isset($data['rpa_id'])) ? $data['rpa_id'] : null;
        $this->rpa_rp_id     = (isset($data['rpa_rp_id'])) ? $data['rpa_rp_id'] : null;
        $this->rpa_aqc_id     = (isset($data['rpa_aqc_id'])) ? $data['rpa_aqc_id'] : null;
        $this->rpa_threat     = (isset($data['rpa_threat'])) ? $data['rpa_threat'] : null;
        $this->rpa_action_plan     = (isset($data['rpa_action_plan'])) ? $data['rpa_action_plan'] : null;
        $this->rpa_policy     = (isset($data['rpa_policy'])) ? $data['rpa_policy'] : null;
        $this->rpa_adr_id     = (isset($data['rpa_adr_id'])) ? $data['rpa_adr_id'] : null;
        $this->rpa_risk_score     = (isset($data['rpa_risk_score'])) ? $data['rpa_risk_score'] : null;
        $this->_rpa_risk_level_sort     = (isset($data['_rpa_risk_level_sort'])) ? $data['_rpa_risk_level_sort'] : null;
        $this->rpa_risk_level     = (isset($data['rpa_risk_level'])) ? $data['rpa_risk_level'] : null;
        $this->rpa_status     = (isset($data['rpa_status'])) ? $data['rpa_status'] : null;
        $this->rpa_contact_u_id     = (isset($data['rpa_contact_u_id'])) ? $data['rpa_contact_u_id'] : null;
        $this->rpa_approver_u_id     = (isset($data['rpa_approver_u_id'])) ? $data['rpa_approver_u_id'] : null;
        $this->rpa_target_date     = (isset($data['rpa_target_date'])) ? $data['rpa_target_date'] : null;
        $this->rpa_active     = (isset($data['rpa_active'])) ? $data['rpa_active'] : null;
        $this->rpa_create_date     = (isset($data['rpa_create_date'])) ? $data['rpa_create_date'] : null;
        $this->_contact_name     = (isset($data['_contact_name'])) ? $data['_contact_name'] : null;
        $this->_approver_name     = (isset($data['_approver_name'])) ? $data['_approver_name'] : null;
        $this->_location_name     = (isset($data['_location_name'])) ? $data['_location_name'] : null;
        $this->_rpa_target_date_formatted     = (isset($data['_rpa_target_date_formatted'])) ? $data['_rpa_target_date_formatted'] : null;
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
                'name'     => 'rpa_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpa_threat',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),

            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpa_contact_u_id',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpa_approver_u_id',
                'required' => false,
            )));


            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpa_status',
                'required' => false,
            )));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}