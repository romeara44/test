<?php

namespace Trainee\Entity;

class Department
{
    /**
     * @var string
     */
    protected $custom_field_1;

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->custom_field_1;
    }

    /**
     * @param string $department
     */
    public function setDepartment($custom_field_1)
    {
        $this->custom_field_1 = $custom_field_1;
    }
}