<?php
namespace Eti\JobeetBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eti\JobeetBundle\Entity\Affiliate;
use Eti\JobeetBundle\Entity\Job;
use Eti\JobeetBundle\Repository\AffiliateRepository;
use Eti\JobeetBundle\Form\AffiliateType;

class AffiliateController extends Controller
{
    public function newAction()
    {
        $entity = new Affiliate();
        $form = $this->createForm(new AffiliateType(), $entity);
 
        return $this->render('EtiJobeetBundle:Affiliate:affiliate_new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    public function createAction(Request $request)
    {
        $affiliate = new Affiliate();
        $form = $this->createForm(new AffiliateType(), $affiliate);
        $form->bind($request);
        $em = $this->getDoctrine()->getManager();
 
        if ($form->isValid()) {
 
            $formData = $request->get('affiliate');
            $affiliate->setUrl($formData['url']);
            $affiliate->setEmail($formData['email']);
            $affiliate->setIsActive(false);
 
            $em->persist($affiliate);
            $em->flush();
 
            return $this->redirect($this->generateUrl('eti_affiliate_wait'));
        }
 
        return $this->render('EtiJobeetBundle:Affiliate:affiliate_new.html.twig', array(
            'entity' => $affiliate,
            'form'   => $form->createView(),
        ));
    }

    public function waitAction()
    {
        return $this->render('EtiJobeetBundle:Affiliate:wait.html.twig');
    }
}