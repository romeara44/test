<?php

namespace Trainee\Service;

interface ILitmosDeleteService
{
    
    /**
     * Remove User From Individualt Team
     */
    public function RemoveUserFromIndividualTeam($userid, $teamid);
    
}