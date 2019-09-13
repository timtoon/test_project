<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PendingProductsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:pending-products';

	private $container;
	private $mailer;
	
	public function __construct(ContainerInterface $container, \Swift_Mailer $mailer)
	{
	    parent::__construct();
	    $this->container = $container;
	    $this->mailer = $mailer;
	}

    protected function configure()
    {
	    $this
	        // the short description shown while running "php bin/console list"
	        ->setDescription('Find overdue pending products.')

	        // the full command description shown when running the command with
	        // the "--help" option
	        ->setHelp('Find app products with a pending status that are over a week old.')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		/* print_r($request->query->all()); */
		$date = new \DateTime('-1 week');

		$entityManager = $this->container->get('doctrine')->getManager();

		$qb = $entityManager->createQueryBuilder();
		
		$results = $qb->select(array('p'))
			->from('App\Entity\Product', 'p')
			->where("p.status = 'pending'")
			->andWhere("p.createdAt < '{$date->format('Y-m-d H:i:s')}'")
			->getQuery()
			->getResult();
			
		$out = "\nID\tISSN\tName\t\tCreatedAt\n======================================================\n";
		
		foreach($results as $result) {
			$out .= $result->getId()."\t".
			$result->getIssn()."\t".
			$result->getName()."\t".
			$result->getCreatedAt()->format('Y-m-d H:i:s')."\n";
		}

		$out .= "Total: ".count($results)."\n";
		
		print $out;

	    $message = ($this->mailer('Overdue Pending Products'))
	        ->setFrom('send@example.com')
	        ->setTo('to@example.com')
	        ->setBody($out, 'text/plain')
	        ;
    }
}