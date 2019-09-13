<?php

namespace App\Controller;

use App\Entity\Customer;
use Exception;
use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\CustomerFormType;
use App\DBAL\EnumStatusType;
use Ramsey\Uuid\Uuid;

class CustomerController extends AbstractController
{
    /**
     * @Route("/customer", name="customer_index", methods={"GET"})
     */
    public function index(Request $request, LoggerInterface $logger)
    {
		$logger->info('Add Customer form...');

        $form = $this->createForm(CustomerFormType::class);
            
        return $this->render('customer/index.html.twig', ['form' => $form->createView(),]);
    }

    /**
     * @Route("/customer", name="customer_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, LoggerInterface $logger)
    {
	    $form = $this->createForm(CustomerFormType::class);
    	$form->handleRequest($request);

    	// Init to empty values to validate below
    	$data = [
    		'first_name' => '',
    		'last_name' => '',
    		'date_of_birth' => '',
		];

        // Account for form submission or an API call
	    if ($form->isSubmitted() && $form->isValid()) {
	        $data = array_merge($data, $form->getData());
        } else if( count($request->query->all()) ) {
	        $data = array_merge($data, $request->query->all());
        } else {
	        return $this->json(['error' => 'Invalid form submission']);
        }

        $customer = new Customer();
        $uuid = Uuid::uuid1()->toString();
        $customer->setUuid($uuid);
        $customer->setFirstName($data['first_name']);
        $customer->setLastName($data['last_name']);
        $customer->setDateOfBirth($data['date_of_birth']);

        $errors = $validator->validate($customer);

        if (count($errors) > 0) {
        	foreach($errors as $error) {
	        	$e = $error->getPropertyPath().': '.$error->getMessage();
        	}
	        return $this->json([
	            'error' => $e,
	        ]);
        }

        try {
	        $em->persist($customer);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to create customer',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info('Adding Customer...');

        return $this->json(['message' => 'Customer {$uuid} created']);
    }

    /**
     * @Route("/customer/{uuid}", name="customer_read", methods={"GET"})
     */
    public function read(string $uuid, EntityManagerInterface $em, LoggerInterface $logger)
    {
		$logger->info("Reading Customer {$uuid}...");

	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $customer = $em->getRepository('App\Entity\Customer')->findOneByUuid($uuid);

        if ( is_null($customer) ) {
	        return $this->json(['error' => 'No customer found for UUID '.$uuid]);
        }

        return $this->json([
        	'uuid'   	   	=> $customer->getUuid(),
        	'first_name'    => $customer->getFirstName(),
        	'first_name'    => $customer->getLastName(),
        	'date_of_birth' => $customer->getDateOfBirth(),
        	'status' 		=> $customer->getStatus(),
        ]);
    }

    /**
     * @Route("/customer/{uuid}", name="customer_update", methods={"PUT","POST","PATCH"})
     * @IsGranted("ROLE_USER")
     */
    public function update($uuid, Request $request, EntityManagerInterface $em, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $customer = $em->getRepository('App\Entity\Customer')->findOneByUuid($uuid);

        if($request->query->has('first_name')) {
	        $customer->setName($request->query->get('first_name'));
        }

        if($request->query->has('last_name')) {
	        $customer->setName($request->query->get('last_name'));
        }

        if($request->query->has('date_of_birth')) {
	        $customer->setName($request->query->get('date_of_birth'));
        }

        if($request->query->has('status')) {
        	if(in_array($request->query->get('status'), EnumStatusType::getAvailableTypes())) {
		        $customer->setStatus($request->query->get('status'));
        	} else {
		        return $this->json(['error' => 'Invalid status type']);
        	}
        }

        $errors = $validator->validate($customer);

        if (count($errors) > 0) {
        	foreach($errors as $error) {
	        	$e = $error->getPropertyPath().': '.$error->getMessage();
        	}
	        return $this->json([
	            'error' => $e,
	        ]);
        }

        try {
	        $em->persist($customer);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to update record',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info("Customer {$uuid} updated...");

        return $this->json(['message' => 'Customer updated']);
    }

    /**
     * @Route("/customer/{uuid}", name="customer_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(string $uuid, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $customer = $em->getRepository('App\Entity\Customer')->findOneByUuid($uuid);

        if (is_null($customer)) {
            throw $this->createNotFoundException('No customer found for id '.$uuid);
        }
        
        $customer->setStatus(EnumStatusType::DELETED);
        $customer->setDeletedAt(new \DateTime());

        try {
	        $em->persist($customer);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to delete record',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info("Customer {$uuid} deleted...");

        return $this->json([
            'message' => 'Record deleted',
        ]);
    }

    /**
     * @Route("/customers", name="customers_findall", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function findAll(LoggerInterface $logger)
    {
		$logger->info('Getting list of all customers...');
		$customers = $this->getDoctrine()
		    ->getRepository('App\Entity\Customer')
		    ->createQueryBuilder('c')
		    ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $this->json($customers);
    }

}