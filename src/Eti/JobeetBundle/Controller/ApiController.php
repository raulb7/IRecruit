<?php
namespace Eti\JobeetBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eti\JobeetBundle\Entity\Affiliate;
use Eti\JobeetBundle\Entity\Job;
use Eti\JobeetBundle\Repository\AffiliateRepository;
 
class ApiController extends Controller
{
    public function listAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
 
        $jobs = array();
 
        $rep = $em->getRepository('EtiJobeetBundle:Affiliate');
        $affiliate = $rep->getForToken($token);
 
        if(!$affiliate) { 
            throw $this->createNotFoundException('This affiliate account does not exist!');
        }
 
        $rep = $em->getRepository('EtiJobeetBundle:Job');
        $activeJobs = $rep->getActiveJobs(null, null, null, $affiliate->getId());
 
        foreach ($activeJobs as $job) {
            $jobs
            [
                $this->get('router')->generate('eti_job_show', [
                    'company' => $job->getCompanySlug(), 
                    'location' => $job->getLocationSlug(), 
                    'id' => $job->getId(), 
                    'position' => $job->getPositionSlug()], true)
                ] = $job->asArray($request->getHost());
        }
 
        $format = $request->getRequestFormat();
        $jsonData = json_encode($jobs);
 
        if ($format == "json") {
            $headers = array('Content-Type' => 'application/json'); 
            $response = new Response($jsonData, 200, $headers);
 
            return $response;
        }
 
        return $this->render('EtiJobeetBundle:Api:jobs.' . $format . '.twig', ['jobs' => $jobs]);  
    }
}