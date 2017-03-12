<?php 
namespace Eti\JobeetBundle\Command;
 
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Eti\JobeetBundle\Entity\Job;
 
class JobeetCleanupCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('eti:jobeet:cleanup')
            ->setDescription('Cleanup Jobeet database')
            ->addArgument('days', InputArgument::OPTIONAL, 'The email', 30)
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getArgument('days');

        $em = $this->getContainer()->get('doctrine')->getManager();

        // cleanup Lucene index
        $index = Job::getLuceneIndex();
 
        $q = $em->getRepository('EtiJobeetBundle:Job')->createQueryBuilder('j')
          ->where('j.expiresAt < :date')
          ->setParameter('date',date('Y-m-d'))
          ->getQuery();
 
        $jobs = $q->getResult();
        foreach ($jobs as $job)
        {
          if ($hit = $index->find('pk:'.$job->getId()))
          {
            $index->delete($hit->id);
          }
        }
 
        $index->optimize();
 
        $output->writeln('Cleaned up and optimized the job index');

        // remove stale jobs
        $nb = $em->getRepository('EtiJobeetBundle:Job')->cleanup($days);

        $output->writeln(sprintf('Removed %d stale jobs', $nb));
    }
}