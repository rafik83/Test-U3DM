<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class ModerationRules
{
    protected $rules;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    public function getRules()
    {
        return $this->rules;
    }
}