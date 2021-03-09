<?php

namespace AppBundle\Event;

use AppBundle\Entity\Project;
use Symfony\Component\EventDispatcher\Event;

class ProjectEvent extends Event
{
    /**
     * Constants
     */
    const ORIGIN_SYSTEM   = 'system';
    const ORIGIN_ADMIN    = 'admin';
    const ORIGIN_MAKER    = 'maker';
    const ORIGIN_CUSTOMER = 'customer';

    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $origin;

    /**
     * ProjectEvent constructor.
     *
     * @param Project $project
     * @param string $origin
     */
    public function __construct(Project $project, $origin = self::ORIGIN_SYSTEM)
    {
        $this->project = $project;
        $this->origin = $origin;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}