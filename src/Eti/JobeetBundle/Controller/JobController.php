<?php

namespace Eti\JobeetBundle\Controller;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Eti\JobeetBundle\Entity\Job;
use Eti\JobeetBundle\Form\JobType;
use Eti\JobeetBundle\Utils\Jobeet;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{

    /**
     * Lists all Job entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $this->getDoctrine()->getRepository('EtiJobeetBundle:Category')->getWithJobs();
        $maxJobsOnHomepage = $this->container->getParameter('max_jobs_on_homepage');
        foreach ($categories as $category) {
            $category->setActiveJobs($em->getRepository('EtiJobeetBundle:Job')->getActiveJobs($category->getId(), $maxJobsOnHomepage));
            $category->setMoreJobs($em->getRepository('EtiJobeetBundle:Job')->countActiveJobs($category->getId()) - $maxJobsOnHomepage);
        }

        $latestJob = $em->getRepository('EtiJobeetBundle:Job')->getLatestPost();
 
        if($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM);
        } else {
            $lastUpdated = new DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }
        
        $format = $this->getRequest()->getRequestFormat();
        return $this->render('EtiJobeetBundle:Job:index.' . $format . '.twig', [
            'categories' => $categories,
            'lastUpdated' => $lastUpdated,
            'feedId' => sha1($this->get('router')->generate('eti_job', ['_format'=> 'atom'], true))
        ]);
    }

    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EtiJobeetBundle:Job')->getActiveJob($id);

        if (! $job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $session = $this->getRequest()->getSession();
 
        // fetch jobs already stored in the job history
        $jobs = $session->get('job_history', []);
     
        // store the job as an array so we can put it in the session and avoid entity serialize errors
        $sessionJob = ['id' => $job->getId(), 'position' =>$job->getPosition(), 
            'company' => $job->getCompany(), 'companySlug' => $job->getCompanySlug(), 
            'locationSlug' => $job->getLocationSlug(), 'positionSlug' => $job->getPositionSlug()];
     
        if (! in_array($sessionJob, $jobs)) {
            // add the current job at the beginning of the array
            array_unshift($jobs, $sessionJob);
     
            // store the new job history back into the session
            $session->set('job_history', array_slice($jobs, 0, 3));
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('EtiJobeetBundle:Job:show.html.twig', array(
            'job'      => $job,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function previewAction($token)
    {
        $em = $this->getDoctrine()->getManager();
 
        $job = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);
 
        if (!$job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }
        
        $publishForm = $this->createPublishForm($job->getToken());
        $deleteForm = $this->createDeleteForm($job->getToken());
        $extendForm = $this->createExtendForm($job->getToken());
 
        return $this->render('EtiJobeetBundle:Job:show.html.twig', array(
            'job'      => $job,
            'delete_form' => $deleteForm->createView(),
            'publish_form' => $publishForm->createView(),
            'extend_form' => $extendForm->createView(),
        ));
    }

    public function publishAction(Request $request, $token)
    {
        $form = $this->createPublishForm($token);
        $form->bind($request);
     
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);
     
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }
     
            $entity->publish();
            $em->persist($entity);
            $em->flush();
     
            $this->get('session')->getFlashBag()->add('notice', 'Your job is now online for 30 days.');
        }
     
        return $this->redirect($this->generateUrl('eti_job_preview', array(
            'company' => $entity->getCompanySlug(),
            'location' => $entity->getLocationSlug(),
            'token' => $entity->getToken(),
            'position' => $entity->getPositionSlug()
        )));
    }
     
    private function createPublishForm($token)
    {
        return $this->createFormBuilder(['token' => $token])
            ->add('token', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);

        if (! $job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        if ($job->getIsActivated()) {
            throw $this->createNotFoundException('Job is activated and cannot be edited.');
        }

        $editForm = $this->createEditForm($job);
        $deleteForm = $this->createDeleteForm($token);

        return $this->render('EtiJobeetBundle:Job:edit.html.twig', array(
            'job'      => $job,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Job entity.
     *
     */
    public function updateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);

        if (!$job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $deleteForm = $this->createDeleteForm($token);
        $editForm = $this->createEditForm($job);
        $editForm->handleRequest($request);

        $oldImagePath = $job->getLogo(); // get path of old image
        if ($editForm->isValid()) {
            if ($editForm->get('file')->getData() !== null) { // if any file was updated
                $file = $editForm->get('file')->getData();
                if ($oldImagePath) {
                    $job->removeFile($oldImagePath); // remove old file, see this at the bottom
                }
                // set logo because preUpload and upload method will not be called if any doctrine
                //  entity will not be changed. It tooks me long time to learn it too.
                $job->setLogo($file->getClientOriginalName()); 
            }
            $em->flush();

            return $this->redirect($this->generateUrl('eti_job_preview', [
                'company' => $job->getCompanySlug(),
                'location' => $job->getLocationSlug(),
                'token' => $job->getToken(),
                'position' => $job->getPositionSlug()
            ]));
        }

        return $this->render('EtiJobeetBundle:Job:edit.html.twig', array(
            'job'      => $job,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    private function createEditForm(Job $entity)
    {
        $form = $this->createForm(new JobType(), $entity, array(
            'action' => $this->generateUrl('eti_job_update', ['token' => $entity->getToken()]),
            'method' => 'POST',
        ));

        $form
            ->add('submit', 'submit', ['label' => 'Update']);

        return $form;
    }
    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('eti_job'));
    }

    public function deleteAjaxAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('EtiJobeetBundle:Job')->findOneById($id);
        $em->remove($job);

        try {
            $em->flush();

            return new JsonResponse(json_encode(['status' => 'success']));
        } catch(\Exception $e) {
            return new JsonResponse(json_encode(['status' => 'fail']));
        }
    }

    /**
     * Creates a form to delete a Job entity by $token
     *
     * @param mixed $token The entity token
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('eti_job_delete', ['token' => $token]))
            ->setMethod('POST')
            ->add('token', 'hidden')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm()
        ;
    }

    public function extendAction(Request $request, $token)
    {
        $form = $this->createExtendForm($token);
        $request = $this->getRequest();
     
        $form->bind($request);
     
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EtiJobeetBundle:Job')->findOneByToken($token);
     
            if (! $entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }
     
            if (! $entity->extend()) {
                throw $this->createNodFoundException('Unable to extend the Job');
            }
     
            $em->persist($entity);
            $em->flush();
     
            $this->get('session')->getFlashBag()->add('notice', sprintf('Your job validity has been extended until %s', $entity->getExpiresAt()->format('m/d/Y')));
        }
     
        return $this->redirect($this->generateUrl('eti_job_preview', [
            'company' => $entity->getCompanySlug(),
            'location' => $entity->getLocationSlug(),
            'token' => $entity->getToken(),
            'position' => $entity->getPositionSlug()
        ]));
    }

    private function createExtendForm($token)
    {
        return $this->createFormBuilder(['token' => $token])
            ->add('token', 'hidden')
            ->getForm();
    }

    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $this->getRequest()->get('query');
        
        if (! $query) {
            if(!$request->isXmlHttpRequest()) {
                return $this->redirect($this->generateUrl('eti_job'));
            } else {
                return new Response('No results.');
            }
        }
 
        $jobs = $em->getRepository('EtiJobeetBundle:Job')->getForLuceneQuery($query);

        if ($request->isXmlHttpRequest()) {
            if('*' == $query || !$jobs || $query == '') {
                return new Response('No results.');
            }
 
            return $this->render('EtiJobeetBundle:Job:list.html.twig', ['jobs' => $jobs]);
        }
 
        return $this->render('EtiJobeetBundle:Job:search.html.twig', ['jobs' => $jobs]);
    }

    /**
     * @Route("/apply/job/{id}", name="eti_apply_job", requirements={"id"="\d+"})
     */
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
        $job = $em->getRepository('EtiJobeetBundle:Job')
            ->findOneById($id);

        $userAlreadyApplied = false;
        foreach ($job->getUsers() as $user) {
            if ($user->getId() == $this->getUser()->getId()) {
                $userAlreadyApplied = true;
            }
        }

        if (!$userAlreadyApplied) {
            $job->addUser($this->getUser());
        }
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your application!';

        $em->persist($job);
        $em->flush();

        return new JsonResponse($response);
    }
}
