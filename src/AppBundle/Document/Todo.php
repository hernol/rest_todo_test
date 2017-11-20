<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Todo
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\TodoRepository")
 */
class Todo {
    /**
     *
     * @MongoDB\Id
     */
    private $id;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     * @Assert\Regex("/^([\w\-.,\s]+)$/")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\Regex("/^([\w\-.,\s]+)$/")
     * @MongoDB\Field(type="string")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @Assert\GreaterThan("today")
     */
    private $due_date;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     * @Assert\DateTime()
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     * @Assert\DateTime()
     */
    private $updated_at;

    /**
     * @var bool
     *
     * @MongoDB\Field(type="boolean")
     */
    private $completed = false;


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
     * Set title
     *
     * @param string $title
     *
     * @return Todo
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Todo
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set due_date
     *
     * @param \DateTime $due_date
     *
     * @return Todo
     */
    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;

        return $this;
    }

    /**
     * Get due_date
     *
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * Set completed
     *
     * @param boolean $completed
     *
     * @return Todo
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return bool
     */
    public function getCompleted()
    {
        return $this->completed;
    }
    
    /**
     * Set created_at
     *
     * @param \DateTime $created_at
     *
     * @return Todo
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    
    /**
     * Set updated_at
     *
     * @param \DateTime $updated_at
     *
     * @return Todo
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

}

