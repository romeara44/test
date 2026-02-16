<?php
namespace Traininglog\Model;;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TraineeUser
{
    public $trainee_user_id;
    public $litmos_id;
    public $user_name;
    public $first_name;
    public $last_name;
    public $full_name;
    public $email;
    public $access_level;
    public $disable_messages;
    public $active;
    public $skype;
    public $phone_work;
    public $phone_mobile;
    public $last_login;
    public $login_key;
    public $is_custom_user_name;
    public $password;
    public $skip_first_login;
    public $time_zone;
    public $sales_force_id;
    public $original_id;
    public $street_1;
    public $street_2;
    public $city;
    public $state;
    public $postal_code;
    public $country;
    public $company_name;
    public $job_title;
    public $custom_field_1;
    public $custom_field_2;
    public $custom_field_3;
    public $custom_field_4;
    public $custom_field_5;
    public $custom_field_6;
    public $custom_field_7;
    public $custom_field_8;
    public $custom_field_9;
    public $custom_field_10;
    public $culture;
    public $salesforce_contact_id;
    public $salesforce_account_id;
    public $created_date;
    public $points;
    public $brand;
    public $manager_id;
    public $manager_name;
    public $enable_text_notification;
    public $website;
    public $twitter;
    public $expiration_date;
    public $job_role;
    public $external_employee_id;
    public $profile_type;    
    //public $creation_date;
    //public $modified_date;


    //protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->trainee_user_id = (isset($data['trainee_user_id'])) ? $data['trainee_user_id'] : null;
        $this->litmos_id = (isset($data['litmos_id'])) ? $data['litmos_id'] : null;
        $this->UserName = (isset($data['UserName'])) ? $data['UserName'] : null;
        $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->last_name  = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->full_name  = (isset($data['full_name'])) ? $data['full_name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->access_level = (isset($data['access_level'])) ? $data['access_level'] : null;
        $this->disable_messages = (isset($data['disable_messages'])) ? $data['disable_messages'] : null;
        $this->active = (isset($data['active'])) ? $data['active'] : null;
        $this->skype = (isset($data['skype'])) ? $data['skype'] : null;
        $this->phone_work = (isset($data['phone_work'])) ? $data['phone_work'] : null;
        $this->phone_mobile = (isset($data['phone_mobile'])) ? $data['phone_mobile'] : null;
        $this->last_login = (isset($data['last_login'])) ? $data['last_login'] : null;
        $this->login_key = (isset($data['login_key'])) ? $data['login_key'] : null;
        $this->is_custom_user_name = (isset($data['is_custom_user_name'])) ? $data['is_custom_user_name'] : null;
        $this->password = (isset($data['password'])) ? $data['password'] : null;
        $this->skip_first_login = (isset($data['skip_first_login'])) ? $data['skip_first_login'] : null;
        $this->time_zone = (isset($data['time_zone'])) ? $data['time_zone'] : null;
        $this->sales_force_id = (isset($data['sales_force_id'])) ? $data['sales_force_id'] : null;
        $this->original_id = (isset($data['original_id'])) ? $data['original_id'] : null;
        $this->street_1 = (isset($data['street_1'])) ? $data['street_1'] : null;
        $this->street_2 = (isset($data['street_2'])) ? $data['street_2'] : null;
        $this->city = (isset($data['city'])) ? $data['city'] : null;
        $this->state = (isset($data['state'])) ? $data['state'] : null;
        $this->postal_code = (isset($data['postal_code'])) ? $data['postal_code'] : null;
        $this->country = (isset($data['country'])) ? $data['country'] : null;
        $this->company_name = (isset($data['company_name'])) ? $data['company_name'] : null;
        $this->job_title = (isset($data['job_title'])) ? $data['job_title'] : null;
        $this->custom_field_1 = (isset($data['custom_field_1'])) ? $data['custom_field_1'] : null;
        $this->custom_field_2 = (isset($data['custom_field_2'])) ? $data['custom_field_2'] : null;
        $this->custom_field_3 = (isset($data['custom_field_3'])) ? $data['custom_field_3'] : null;
        $this->custom_field_4 = (isset($data['custom_field_4'])) ? $data['custom_field_4'] : null;
        $this->custom_field_5 = (isset($data['custom_field_5'])) ? $data['custom_field_5'] : null;
        $this->custom_field_6 = (isset($data['custom_field_6'])) ? $data['custom_field_6'] : null;
        $this->custom_field_7 = (isset($data['custom_field_7'])) ? $data['custom_field_7'] : null;
        $this->custom_field_8 = (isset($data['custom_field_8'])) ? $data['custom_field_8'] : null;
        $this->custom_field_9 = (isset($data['custom_field_9'])) ? $data['custom_field_9'] : null;
        $this->custom_field_10 = (isset($data['custom_field_10'])) ? $data['custom_field_10'] : null;
        $this->culture = (isset($data['culture'])) ? $data['culture'] : null;
        $this->salesforce_contact_id = (isset($data['salesforce_contact_id'])) ? $data['salesforce_contact_id'] : null;
        $this->salesforce_account_id = (isset($data['salesforce_account_id'])) ? $data['salesforce_account_id'] : null;
        $this->created_date = (isset($data['created_date'])) ? $data['created_date'] : null;
        $this->points = (isset($data['points'])) ? $data['points'] : null;
        $this->brand = (isset($data['brand'])) ? $data['brand'] : null;
        $this->manager_id = (isset($data['manager_id'])) ? $data['manager_id'] : null;
        $this->manager_name = (isset($data['manager_name'])) ? $data['manager_name'] : null;
        $this->enable_text_notification = (isset($data['enable_text_notification'])) ? $data['enable_text_notification'] : null;
        $this->website = (isset($data['website'])) ? $data['website'] : null;
        $this->twitter = (isset($data['twitter'])) ? $data['twitter'] : null;
        $this->expiration_date = (isset($data['expiration_date'])) ? $data['expiration_date'] : null;
        $this->job_role = (isset($data['job_role'])) ? $data['job_role'] : null;
        $this->external_employee_id = (isset($data['external_employee_id'])) ? $data['external_employee_id'] : null;
        $this->profile_type = (isset($data['profile_type'])) ? $data['profile_type'] : null;
        $this->profile_type = (isset($data['profile_type'])) ? $data['profile_type'] : null;
        //$this->creation_date        = (isset($data['creation_date']))      ? $data['creation_date']      : null;
        //$this->modified_date        = (isset($data['modified_date']))      ? $data['modified_date']      : null;

    }
    // public function exchangeObject($data)
    // {
    //     $this->audit_record_id          = (isset($data->audit_record_id)) ? $data->audit_record_id : null;

    //     $this->trainee_user_id = (isset($data['trainee_user_id'])) ? $data['trainee_user_id'] : null;
    //     $this->litmos_id = (isset($data['litmos_id'])) ? $data['litmos_id'] : null;
    //     $this->UserName = (isset($data['UserName'])) ? $data['UserName'] : null;
    //     $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
    //     $this->last_name  = (isset($data['last_name'])) ? $data['last_name'] : null;
    //     $this->full_name  = (isset($data['full_name'])) ? $data['full_name'] : null;
    //     $this->email = (isset($data['email'])) ? $data['email'] : null;
    //     $this->access_level = (isset($data['access_level'])) ? $data['access_level'] : null;
    //     $this->disable_messages = (isset($data['disable_messages'])) ? $data['disable_messages'] : null;
    //     $this->active = (isset($data['active'])) ? $data['active'] : null;
    //     $this->skype = (isset($data['skype'])) ? $data['skype'] : null;
    //     $this->phone_work = (isset($data['phone_work'])) ? $data['phone_work'] : null;
    //     $this->phone_mobile = (isset($data['phone_mobile'])) ? $data['phone_mobile'] : null;
    //     $this->last_login = (isset($data['last_login'])) ? $data['last_login'] : null;
    //     $this->login_key = (isset($data['login_key'])) ? $data['login_key'] : null;
    //     $this->is_custom_user_name = (isset($data['is_custom_user_name'])) ? $data['is_custom_user_name'] : null;
    //     $this->password = (isset($data['password'])) ? $data['password'] : null;
    //     $this->skip_first_login = (isset($data['skip_first_login'])) ? $data['skip_first_login'] : null;
    //     $this->time_zone = (isset($data['time_zone'])) ? $data['time_zone'] : null;
    //     $this->sales_force_id = (isset($data['sales_force_id'])) ? $data['sales_force_id'] : null;
    //     $this->original_id = (isset($data['original_id'])) ? $data['original_id'] : null;
    //     $this->street_1 = (isset($data['street_1'])) ? $data['street_1'] : null;
    //     $this->street_2 = (isset($data['street_2'])) ? $data['street_2'] : null;
    //     $this->city = (isset($data['city'])) ? $data['city'] : null;
    //     $this->state = (isset($data['state'])) ? $data['state'] : null;
    //     $this->postal_code = (isset($data['postal_code'])) ? $data['postal_code'] : null;
    //     $this->country = (isset($data['country'])) ? $data['country'] : null;
    //     $this->company_name = (isset($data['company_name'])) ? $data['company_name'] : null;
    //     $this->job_title = (isset($data['job_title'])) ? $data['job_title'] : null;
    //     $this->custom_field_1 = (isset($data['custom_field_1'])) ? $data['custom_field_1'] : null;
    //     $this->custom_field_2 = (isset($data['custom_field_2'])) ? $data['custom_field_2'] : null;
    //     $this->custom_field_3 = (isset($data['custom_field_3'])) ? $data['custom_field_3'] : null;
    //     $this->custom_field_4 = (isset($data['custom_field_4'])) ? $data['custom_field_4'] : null;
    //     $this->custom_field_5 = (isset($data['custom_field_5'])) ? $data['custom_field_5'] : null;
    //     $this->custom_field_6 = (isset($data['custom_field_6'])) ? $data['custom_field_6'] : null;
    //     $this->custom_field_7 = (isset($data['custom_field_7'])) ? $data['custom_field_7'] : null;
    //     $this->custom_field_8 = (isset($data['custom_field_8'])) ? $data['custom_field_8'] : null;
    //     $this->custom_field_9 = (isset($data['custom_field_9'])) ? $data['custom_field_9'] : null;
    //     $this->custom_field_10 = (isset($data['custom_field_10'])) ? $data['custom_field_10'] : null;
    //     $this->culture = (isset($data['culture'])) ? $data['culture'] : null;
    //     $this->salesforce_contact_id = (isset($data['salesforce_contact_id'])) ? $data['salesforce_contact_id'] : null;
    //     $this->salesforce_account_id = (isset($data['salesforce_account_id'])) ? $data['salesforce_account_id'] : null;
    //     $this->created_date = (isset($data['created_date'])) ? $data['created_date'] : null;
    //     $this->points = (isset($data['points'])) ? $data['points'] : null;
    //     $this->brand = (isset($data['brand'])) ? $data['brand'] : null;
    //     $this->manager_id = (isset($data['manager_id'])) ? $data['manager_id'] : null;
    //     $this->manager_name = (isset($data['manager_name'])) ? $data['manager_name'] : null;
    //     $this->enable_text_notification = (isset($data['enable_text_notification'])) ? $data['enable_text_notification'] : null;
    //     $this->website = (isset($data['website'])) ? $data['website'] : null;
    //     $this->twitter = (isset($data['twitter'])) ? $data['twitter'] : null;
    //     $this->expiration_date = (isset($data['expiration_date'])) ? $data['expiration_date'] : null;
    //     $this->job_role = (isset($data['job_role'])) ? $data['job_role'] : null;
    //     $this->external_employee_id = (isset($data['external_employee_id'])) ? $data['external_employee_id'] : null;
    //     $this->profile_type = (isset($data['profile_type'])) ? $data['profile_type'] : null;
    //     $this->profile_type = (isset($data['profile_type'])) ? $data['profile_type'] : null;
    //     //$this->creation_date        = (isset($data['creation_date']))      ? $data['creation_date']      : null;
    //     //$this->modified_date        = (isset($data['modified_date']))      ? $data['modified_date']      : null;

    // }


    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}