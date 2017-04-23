<?php
namespace Eti\JobeetBundle\Controller;
 
use Eti\JobeetBundle\Entity\UProfile;
use Eti\JobeetBundle\Entity\User;
use Eti\JobeetBundle\Form\CompanyType;
use Eti\JobeetBundle\Form\JobType;
use Eti\JobeetBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eti\JobeetBundle\Entity\Job;

class CompanyController extends Controller
{

    /**
     * @Route("profile/{id}", name="viewProfile", requirements={"id": "\d+"})
     */
    public function viewProfileAction(Request $request, Profile $profile)
    {
        return $this->render('EtiJobeetBundle:User:profile.html.twig', array(
            'profile' => $profile,
            'user' => $profile->getUser()
        ));
    }

    public function dashboardAction(Request $request)
    {
        $em = $this->get("doctrine.orm.entity_manager");
        $profile = $this->getUser()->getProfile();
        $form = $this->createForm(new CompanyType(), $profile, ['email' => $this->getUser()->getEmail()]);
        $form->handleRequest($request);
        $isSuccess = false;
        if($form->isValid())
        {
            @unlink($this->getParameter('uploaddir')."/".$profile->getImage());
            $imageFile = $form->get('imageFile')->getData();
            if($imageFile)
            {
                $imageName = md5(uniqid()).'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploaddir'),
                    $imageName
                );
                $profile->setImage($imageName);
            }
            $em->merge($profile);
            $em->flush();
            $email = $form->get('email')->getData();
            $user = $this->getUser();
            $user->setEmail($email);
            $em->merge($user);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Your profile information was successfully updated!');
            $isSuccess = true;
        }


        return $this->render('@EtiJobeet/Company/companydash.html.twig', ['form' => $form->createView(), 'success' => $isSuccess]);
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
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('company_dashboard'));
        }

        return $this->render('@EtiJobeet/Company/Job/new.html.twig', array(
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

        $form->add('submit', 'submit', array('label' => 'CREATE JOB'));

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
    /**
     * @Route("/job/modaldisplay/{id}", name="eti_job_modal_display")
     */
    public function newJobModalDisplayAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('EtiJobeetBundle:Job')->findOneById($id);

        return $this->render('@EtiJobeet/Company/Job/modalDisplay.html.twig', [
            'job' => $job
        ]);
    }

    /**
     * @Route("/cv/{id}", name="viewPDF", requirements={"id": "\d+"})
     */
    public function viewPDFAction(User $user)
    {
        $path = $this->getParameter('uploaddir').'\\';
        $profile = $user->getProfile();
        $path.= $profile->getCurriculumVitae();

        return new BinaryFileResponse($path);
    }
}

