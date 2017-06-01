<?php

namespace IRecruitBundle\Controller;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use IRecruitBundle\Entity\Job;
use IRecruitBundle\Form\JobType;
use IRecruitBundle\Utils\IRecruit;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{
    public function applyJobAction(Request $request, $id)
    {
        $response = [
            'status' => 'fail'
        ];
        if (!$this->getUser()) {
            $response['message'] = 'You must be logged in to perform this action!';

            return new JsonResponse($response);
        }

        if ($this->isGranted('ROLE_COMPANY')) {
            $response['message'] = 'Companies cannot apply to jobs!';

            return new JsonResponse($response);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $job = $em->getRepository('IRecruitBundle:Job')
            ->findOneById($id);

        $userAlreadyApplied = false;
        foreach ($job->getUsers() as $user)
        {
            if ($user->getId() == $this->getUser()->getId())
            {
                $userAlreadyApplied = true;
            }
        }

        if (!$userAlreadyApplied) {
            $job->addUser($this->getUser());
        }
        else
        {
            $response['status'] = 'success';
            $response['message'] = 'You already applied for this job!';
            return new JsonResponse($response);
        }
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your application!';

        $em->persist($job);
        $em->flush();

        return new JsonResponse($response);
    }
}
