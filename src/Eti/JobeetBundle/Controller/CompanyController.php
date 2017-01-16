<?php
namespace Eti\JobeetBundle\Controller;
 
use Eti\JobeetBundle\Form\JobType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eti\JobeetBundle\Entity\Affiliate;
use Eti\JobeetBundle\Entity\Job;
use Eti\JobeetBundle\Repository\AffiliateRepository;
use Eti\JobeetBundle\Form\AffiliateType;

class CompanyController extends Controller
{
    public function dashboardAction()
    {
        $entity = new Affiliate();
        $form = $this->createForm(new AffiliateType(), $entity);
 
        return $this->render('EtiJobeetBundle:Company:companydash.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    public function jobListAction(Request $request)
    {
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT j FROM EtiJobeetBundle:Job j WHERE j.isActivated=1" ;
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $this->getParameter('pagination')['per_page']/*limit per page*/
        );

        // parameters to template
        return $this->render('EtiJobeetBundle:Company:jobLists.html.twig', array(
            'pagination' => $pagination,
        ));
    }
    /**
     * Creates a new Job entity.
     *
     */
    public function createJobAction(Request $request)
    {
        $entity = new Job();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        var_dump($form->getData());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('eti_job_preview', [
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'token' => $entity->getToken(),
                'position' => $entity->getPositionSlug()
            ]));
        }

        return $this->render('EtiJobeetBundle:Job:new.html.twig', array(
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
            'action' => $this->generateUrl('eti_job_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * @Route("/company/job/new", name="eti_job_new")
     */
    public function newJobAction()
    {
        $entity = new Job();
        $entity->setType('full-time');
        $form   = $this->createCreateForm($entity);

        return $this->render('@EtiJobeet/Company/Job/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
}

