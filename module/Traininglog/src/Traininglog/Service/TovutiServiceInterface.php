<?php
 // Filename: /module/Traininglog/src/Traininglog/Service/TovutiServiceInterface.php
 namespace Traininglog\Service;

 use Traininglog\Model\TovutiInterface;

 interface TovutiServiceInterface
 {
  

    /**
      * Retrieve TovutiGroup from Tovuti by TovutiUserGroupId.
      *
      * @param  array $tovutiUserGroupId Tovuti user group ID
      * @return TovutiServiceInterface
    */
    public function GetUserGroupFromTovuti($tovutiUserGroupId);

    /**
      * Retrieve user from Tovuti using the tovuti id.
      *
      * @param  int $tovutiId Identifier of the Tovuti user that should be returned
      * @return TovutiServiceInterface
    */
    public function GetTovutiUser($tovutiId);

    /**
      * Parse TovutiCustomField from the CustomField array in the Tovuti response model.
      *
      * @param  array $customFields Tovuti customFields array object
      * @param  int $field Custom Field number
      * @return TovutiServiceInterface
    */
    public function ParseTovutiCustomField($customFields, $field);

    /**
      * Translate a Tovuti response to a TovutiUser Model object.
      *
      * @param  int $tovutiUserResponse Tovuti object to parse and tranlate to a TovutiUser model object and then return that object
      * @return TovutiServiceInterface
    */
    public function SetTovutiUser($tovutiUserResponse);
}