<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="project_file")
 * @ORM\Entity()
 */
class ProjectFile
{
    /**
     * Hook timestampable behavior
     * Updates createdAt and updatedAt fields
     */
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project", inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_original", type="string", length=255, nullable=true)
     */
    private $originalName;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set project
     *
     * @param Project $project
     *
     * @return ProjectFile
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProjectFile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set original name
     *
     * @param string|null $name
     *
     * @return ProjectFile
     */
    public function setOriginalName($name = null)
    {
        $this->originalName = $name;

        return $this;
    }

    /**
     * Get original name
     *
     * @return string|null
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }
}
