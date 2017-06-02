<?php

namespace IRecruitBundle\Controller;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use IRecruitBundle\Entity\Job;
use IRecruitBundle\Form\JobType;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /**
     * Creates a new Job entity.
     *
     */
    public function createJobAction(Request $request)
    {
        if(!$this->isGranted('ROLE_COMPANY'))
            throw new AccessDeniedException();

        $entity = new Job();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setCompany($this->getUser()->getCompany());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('company_dashboard'));
        }

        return $this->render('@IRecruit/Company/Job/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Job entity.
     *
     * @param Job $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Job $entity)
    {
        $form = $this->createForm(new JobType(), $entity, array(
            'action' => $this->generateUrl('jobCreate'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'CREATE JOB'));

        return $form;
    }

    public function newJobAction()
    {
        $entity = new Job();
        $entity->setType('full-time');
        $form   = $this->createCreateForm($entity);

        return $this->render('@IRecruit/Company/Job/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

}
