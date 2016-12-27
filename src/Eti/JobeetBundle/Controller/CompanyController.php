<?php
namespace Eti\JobeetBundle\Controller;
 
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

}

