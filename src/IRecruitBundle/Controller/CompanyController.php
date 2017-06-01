<?php
namespace IRecruitBundle\Controller;
 
use IRecruitBundle\Entity\CProfile;
use IRecruitBundle\Entity\User;
use IRecruitBundle\Form\CompanyType;
use IRecruitBundle\Form\JobType;
use IRecruitBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use IRecruitBundle\Entity\Job;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompanyController extends Controller
{
    public function viewProfileAction(Request $request, CProfile $profile)
    {
        return $this->render('IRecruitBundle:User:profile.html.twig', array(
            'profile' => $profile,
            'user' => $profile->getUser()
        ));
    }

    public function dashboardAction(Request $request)
    {
        $em = $this->get("doctrine.orm.entity_manager");
        $profile = $this->getUser()->getCompany();
        $form = $this->createForm(new CompanyType(), $profile, ['email' => $this->getUser()->getEmail()]);
        $form->handleRequest($request);
        $isSuccess = false;
        if($form->isValid())
        {
            @unlink($this->getParameter('uploaddir')."/".$profile->getLogo());
            $logo = $form->get('logo')->getData();
            if($logo)
            {
                $imageName = md5(uniqid()).'.'.$logo->guessExtension();
                $logo->move(
                    $this->getParameter('uploaddir'),
                    $imageName
                );
                $profile->setLogo($imageName);
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


        return $this->render('@IRecruit/Company/companydash.html.twig', ['form' => $form->createView(), 'success' => $isSuccess]);
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
            'action' => $this->generateUrl('eti_job_create'),
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
    
    public function profileAction()
    {
        return $this->render('@IRecruit/User/profile.html.twig');
    }
    
    public function newJobModalDisplayAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('IRecruitBundle:Job')->findOneById($id);

        return $this->render('@IRecruit/Company/Job/modalDisplay.html.twig', [
            'job' => $job
        ]);
    }
    
    public function viewPDFAction(User $user)
    {
        $path = $this->getParameter('uploaddir').'\\';
        $profile = $user->getProfile();
        $path.= $profile->getCurriculumVitae();

        return new BinaryFileResponse($path);
    }
    
    public function listAction()
    {
        return $this->render('@IRecruit/Company/companyJobs.html.twig', array());
    }
}