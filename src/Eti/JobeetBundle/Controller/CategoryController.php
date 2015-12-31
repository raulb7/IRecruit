<?php
namespace Eti\JobeetBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Eti\JobeetBundle\Entity\Category;
 
class CategoryController extends Controller
{
    public function showAction($slug, $page)
    {
        $em = $this->getDoctrine()->getManager();
 
        $category = $em->getRepository('EtiJobeetBundle:Category')->findOneBySlug($slug);
     
        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $latestJob = $em->getRepository('EtiJobeetBundle:Job')->getLatestPost($category->getId());
 
        if ($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM); 
        } else {
            $lastUpdated = new DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }
        
        $totalJobs = $em->getRepository('EtiJobeetBundle:Job')->countActiveJobs($category->getId());
        $jobsPerPage = $this->container->getParameter('max_jobs_on_category');
        $lastPage = ceil($totalJobs / $jobsPerPage);
        $previousPage = $page > 1 ? $page - 1 : 1;
        $nextPage = $page < $lastPage ? $page + 1 : $lastPage;
        $category->setActiveJobs($em->getRepository('EtiJobeetBundle:Job')
            ->getActiveJobs($category->getId(), $jobsPerPage, ($page - 1) * $jobsPerPage));
        
        $format = $this->getRequest()->getRequestFormat();
        return $this->render('EtiJobeetBundle:Category:show.' . $format . '.twig', [
            'category' => $category,
            'lastPage' => $lastPage,
            'previousPage' => $previousPage,
            'currentPage' => $page, 
            'nextPage' => $nextPage,
            'totalJobs' => $totalJobs,
            'feedId' => sha1($this->get('router')->generate('eti_jobeet_category', 
                ['slug' => $category->getSlug(), 'format' => 'atom'], true)),
            'lastUpdated' => $lastUpdated
        ]);
    }
}