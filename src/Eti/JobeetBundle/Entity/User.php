<?php

namespace Eti\JobeetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->jobs = new ArrayCollection();
    }
    /**
     * @var \Eti\JobeetBundle\Entity\Company
     */
    private $company;

    /**
     * @var \Eti\JobeetBundle\Entity\Profile
     */
    private $profile;


    /**
     * Set company
     *
     * @param \Eti\JobeetBundle\Entity\Company $company
     *
     * @return User
     */
    public function setCompany(\Eti\JobeetBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Eti\JobeetBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set profile
     *
     * @param \Eti\JobeetBundle\Entity\Profile $profile
     *
     * @return User
     */
    public function setProfile(\Eti\JobeetBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Eti\JobeetBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $jobs;


    /**
     * Add job
     *
     * @param \Eti\JobeetBundle\Entity\Job $job
     *
     * @return User
     */
    public function addJob(\Eti\JobeetBundle\Entity\Job $job)
    {
        $this->jobs[] = $job;

        return $this;
    }

    /**
     * Remove job
     *
     * @param \Eti\JobeetBundle\Entity\Job $job
     */
    public function removeJob(\Eti\JobeetBundle\Entity\Job $job)
    {
        $this->jobs->removeElement($job);
    }

    /**
     * Get jobs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
