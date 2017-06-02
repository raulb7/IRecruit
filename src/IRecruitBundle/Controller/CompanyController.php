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
        return $this->render('@IRecruit/Company/pcprofile.html.twigcprofile.html.twig', array(
            'profile' => $profile,
            'user' => $profile->getUser()
        ));
    }

    public function profileAction()
    {
        return $this->render('@IRecruit/Company/cprofile.html.twig');
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