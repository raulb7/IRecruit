<?php

namespace Eti\JobeetBundle\Controller;

use Eti\JobeetBundle\Entity\CProfile;
use Eti\JobeetBundle\Entity\UProfile;
use Eti\JobeetBundle\Entity\User;
use Eti\JobeetBundle\Form\RegisterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
 
        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }
 
        return $this->render('EtiJobeetBundle:Default:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(Security::LAST_USERNAME),
            'error'         => $error,
        ));
    }
    /**
     * @Template("@EtiJobeet/Default/homepage.html.twig")
     * @Route("/home", name="homepage")
     */
    public function homepageAction()
    {
        return [
        ];
    }
    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction(Request $request)
    {
        $userType = $request->query->get('user_type');
        $formData = new \stdClass();
        $em = $this->getDoctrine()->getManager();
        if ($userType == 1) {
            $formData->email = null;
            $formData->username = null;
            $formData->password = null;
            $formData->country = null;
            $formData->city = null;
            $formData->firstName = null;
            $formData->lastName = null;
            $formData->birthDate = null;
            $formData->phone = null;
            $formData->gender = null;
            $formData->homeAddress = null;
            $formData->picture = null;
            $formData->curriculumVitae = null;
        } else {
            $formData->email2 = null;
            $formData->username2 = null;
            $formData->password2 = null;
            $formData->country2 = null;
            $formData->city2 = null;
            $formData->companyName = null;
            $formData->domain = null;
            $formData->headquarterAddress = null;
            $formData->companyLogo = null;
            $formData->companyUrl = null;
        }
        $form = $this->createForm(new RegisterType(),$formData,['userType' => $userType ]);
        $form->handleRequest($request);

        $message = "";
        if($form->isValid()){
            $formData = $form->getData();
            try{
                if($userType == 1){
                    $user = new User();
                    $user->setUsername($formData->username);
                    $user->setEmail($formData->email);
                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $formData->password);
                    $user->setPassword($encoded);
                    $user->setRoles(['ROLE_USER']);
                    $user->setEnabled(true);
                    $profile = new Profile();
                    $profile->setUser($user);
                    $profile->setCountry($formData->country);
                    $profile->setCity($formData->city);
                    $profile->setFirstName($formData->firstName);
                    $profile->setLastName($formData->lastName);
                    $profile->setBirthDate($formData->birthDate);
                    $profile->setPhone($formData->phone);
                    $profile->setGender($formData->gender);
                    $profile->setAddress($formData->homeAddress);
                    $profile->setImage($formData->picture);

                    $picture = $formData->picture;
                    $imageName = md5(uniqid()).'.'.$picture->guessExtension();
                    $picture->move(
                        $this->getParameter('uploaddir'),
                        $imageName
                    );
                    $profile->setImage($imageName);

                    $cv = $formData->curriculumVitae;
                    $pdfName = md5(uniqid()).'.'.$cv->guessExtension();
                    $cv->move(
                        $this->getParameter('uploaddir'),
                        $pdfName
                    );
                    $profile->setCurriculumVitae($pdfName);

                    $profile->setCreatedAt(new \Datetime());
                    $profile->setUpdatedAt(new \Datetime());
                    $em->persist($profile);
                    $em->flush();
                } else {
                    $user = new User();
                    $user->setUsername($formData->username2);
                    $user->setEmail($formData->email2);
                    $plainPassword = $formData->password2;
                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($encoded);
                    $user->setRoles(['ROLE_USER', 'ROLE_COMPANY']);
                    $user->setEnabled(true);
                    $company = new Company();
                    $company->setUser($user);
                    $company->setEmail($formData->email2);
                    $company->setCountry($formData->country2);
                    $company->setCity($formData->city2);
                    $company->setName($formData->companyName);
                    $company->setActivityDomain($formData->domain);
                    $company->setAddress($formData->headquarterAddress);
                    $image = $formData->companyLogo;
                    $imageName = md5(uniqid()).'.'.$image->guessExtension();
                    $image->move(
                        $this->getParameter('uploaddir'),
                        $imageName
                    );
                    $company->setLogo($imageName);
                    $company->setUrl($formData->companyUrl);
                    $company->setCreatedAt(new \Datetime());
                    $company->setUpdatedAt(new \Datetime());
                    $em->persist($company);
                    $em->flush();
                }
                $message = "success";
            } catch(\Exception $e){
                $message="Registration failed. Please try again!";
            };
        }
        
        return new JsonResponse([
            'template' => $this->renderView('@EtiJobeet/Default/register.html.twig',[
                            'form' => $form->createView(),
                            'userType' => $userType
                ]),
            'message' => $message,
            'userType' => $userType
        ]);
    }

    public function testAction()
    {
       return $this->render('test.html.twig', [
        ]);
    }
    public function jobInterfaceAction()
    {
        return $this->render('@EtiJobeet/Job/jobInterface.html.twig');
    }
}
