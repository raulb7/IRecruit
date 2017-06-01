<?php

namespace IRecruitBundle\Entity;

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
     * @var \IRecruitBundle\Entity\CProfile
     */
    private $company;

    /**
     * @var \IRecruitBundle\Entity\UProfile
     */
    private $profile;


    /**
     * Set company
     *
     * @param \IRecruitBundle\Entity\CProfile $company
     *
     * @return User
     */
    public function setCompany(\IRecruitBundle\Entity\CProfile $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \IRecruitBundle\Entity\CProfile
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set profile
     *
     * @param \IRecruitBundle\Entity\UProfile $profile
     *
     * @return User
     */
    public function setProfile(\IRecruitBundle\Entity\UProfile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \IRecruitBundle\Entity\UProfile
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
     * @param \IRecruitBundle\Entity\Job $job
     *
     * @return User
     */
    public function addJob(\IRecruitBundle\Entity\Job $job)
    {
        $this->jobs[] = $job;

        return $this;
    }

    /**
     * Remove job
     *
     * @param \IRecruitBundle\Entity\Job $job
     */
    public function removeJob(\IRecruitBundle\Entity\Job $job)
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
