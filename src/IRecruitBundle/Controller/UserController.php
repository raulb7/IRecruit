<?php
namespace IRecruitBundle\Controller;

use IRecruitBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use IRecruitBundle\Entity\UProfile;

use Symfony\Component\Validator\Constraints as Assert;

class UserController extends Controller
{
    public function profileAction()
    {
        return $this->render('@IRecruit/User/uprofile.html.twig');
    }

    public function viewProfileAction(Request $request, UProfile $profile)
    {
        return $this->render('@IRecruit/User/puprofile.html.twig', array(
            'profile' => $profile,
            'user' => $profile->getUser()
        ));
    }

    public function dashboardAction(Request $request)
    {
        $em = $this->get("doctrine.orm.entity_manager");
        $profile = $this->getUser()->getProfile();
        $form = $this->createForm(new ProfileType(), $profile, ['email' => $this->getUser()->getEmail()]);
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

        return $this->render('@IRecruit/User/userdash.html.twig', ['form' => $form->createView(), 'success' => $isSuccess]);
    }

    public function uploadCvAction(Request $request)
    {
        $em = $this->get("doctrine.orm.entity_manager");
        $profile = $this->getUser()->getProfile();
        $formData = new \stdClass();
        $formData->curriculumVitae = NULL;
        $form = $this->createFormBuilder($formData)
            ->add('curriculumVitae', 'file', [
                'constraints' => [
                    new Assert\File(array(
                        'maxSize' => '30M',
                        'mimeTypes' => array(
                            'application/pdf',
                            'application/x-pdf',
                        ))),
                    new Assert\NotBlank(),
                ]
            ])
            ->getForm();
        ;
        $form->handleRequest($request);
        $isSuccess = false;

        if($form->isValid()){
            $isSuccess = true;
            @unlink($this->getParameter('uploaddir')."/".$profile->getCurriculumVitae());
            $formData = $form->getData();
            $cv = $formData->curriculumVitae;
            $pdfName = md5(uniqid()).'.'.$cv->guessExtension();
            $cv->move(
                $this->getParameter('uploaddir'),
                $pdfName
            );
            $profile->setCurriculumVitae($pdfName);
            $em->merge($profile);
            $em->flush();
        }

        return $this->render("@IRecruit/User/uploadcv.html.twig",["form"=>$form->createView(),"isSuccess"=>$isSuccess]);
    }
    
    public function viewPDFAction()
    {
        $user = $this->getUser();
        if($user && $this->isGranted('ROLE_COMPANY'))
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();

        $path = $this->getParameter('uploaddir').'\\';
        $profile = $user->getProfile();
        $path.= $profile->getCurriculumVitae();

        return new BinaryFileResponse($path);
    }
}

