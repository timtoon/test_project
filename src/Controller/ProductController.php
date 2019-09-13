<?php

namespace App\Controller;

use App\Entity\Product;
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
use App\Form\ProductFormType;
use App\DBAL\EnumStatusType;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, LoggerInterface $logger)
    {
		$logger->info('Add Product form...');

        $form = $this->createForm(ProductFormType::class);
            
        return $this->render('product/index.html.twig', ['form' => $form->createView(),]);
    }

    /**
     * @Route("/product", name="product_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, LoggerInterface $logger)
    {
	    $form = $this->createForm(ProductFormType::class);
    	$form->handleRequest($request);

    	// Init to empty values to validate below
    	$data = [
    		'issn' => '',
    		'name' => '',
    		'customer_uuid' => '',
		];

        // Account for form submission or an API call
	    if ($form->isSubmitted() && $form->isValid()) {
	        $data = array_merge($data, $form->getData());
        } else if( count($request->query->all()) ) {
	        $data = array_merge($data, $request->query->all());
        } else {
	        return $this->json(['error' => 'Invalid form submission']);
        }

        $customer = $em->getRepository('App\Entity\Customer')->findOneBy(['uuid' => $data['customer_uuid']]);

        if( is_null($customer) ) {
	        return $this->json(['error' => 'Customer not found']);
        }

        $product = new Product();
        $product->setIssn($data['issn']);
        $product->setName($data['name']);
        $product->setCustomer($customer);

        $errors = $validator->validate($product);

        if (count($errors) > 0) {
        	foreach($errors as $error) {
	        	$e = $error->getPropertyPath().': '.$error->getMessage();
        	}
	        return $this->json([
	            'error' => $e,
	        ]);
        }

        try {
	        $em->persist($product);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to create product',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info('Adding Product...');

        return $this->json(['message' => 'Entity created']);
    }

    /**
     * @Route("/product/{issn}", name="product_read", methods={"GET"})
     */
    public function read(string $issn, EntityManagerInterface $em, LoggerInterface $logger)
    {
		$logger->info("Reading Product {$issn}...");

	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $product = $em->getRepository('App\Entity\Product')->findOneByIssn($issn);

        if ( is_null($product) ) {
	        return $this->json(['error' => 'No product found for ISSN '.$issn]);
        }

        return $this->json([
        	'issn'   => $product->getIssn(),
        	'name'   => $product->getName(),
        	'status' => $product->getStatus(),
        ]);
    }

    /**
     * @Route("/product/{issn}", name="product_update", methods={"PUT","POST","PATCH"})
     * @IsGranted("ROLE_USER")
     */
    public function update($issn, Request $request, EntityManagerInterface $em, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $product = $em->getRepository('App\Entity\Product')->findOneByIssn($issn);

        if($request->query->has('name')) {
	        $product->setName($request->query->get('name'));
        }

        if($request->query->has('status')) {
	        $product->setStatus($request->query->get('status'));
        }

        if($request->query->has('customer_uuid')) {
	        $customer = $entityManager->getRepository('App\Entity\Customer')->findOneBy(['uuid' => $request->query->get('customer_uuid')]);

	        if( is_null($customer) ) {
		        return $this->json(['error' => 'Customer not found']);
	        }
	        $product->setCustomer($request->query->get('customer_uuid'));
        }

        $errors = $validator->validate($product);

        if (count($errors) > 0) {
        	foreach($errors as $error) {
	        	$e = $error->getPropertyPath().': '.$error->getMessage();
        	}
	        return $this->json([
	            'error' => $e,
	        ]);
        }

        try {
	        $em->persist($product);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to update record',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info("Product {$issn} updated...");

        return $this->json(['message' => 'Entity updated']);
    }

    /**
     * @Route("/product/{issn}", name="product_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(string $issn, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $product = $entityManager->getRepository('App\Entity\Product')->findOneBy(['issn' => $issn]);

        if (is_null($product)) {
            throw $this->createNotFoundException('No product found for id '.$issn);
        }
        
        $product->setStatus(EnumStatusType::DELETED);
        $product->setDeletedAt(new \DateTime());

//        $entityManager->remove($guest);
//        $entityManager->flush();

        try {
	        $em->persist($product);
	        $em->flush();
        } catch (Exception $e) {
	        return $this->json([
	            'error' => 'Unable to delete record',
	            'message' => $e->getMessage(),
	        ]);
        }

		$logger->info("Product {$issn} deleted...");

        return $this->json([
            'message' => 'Record deleted',
        ]);
    }

    /**
     * @Route("/products", name="product_findall", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function findAll(LoggerInterface $logger)
    {
		$logger->info('Getting list of all Products...');
		$products = $this->getDoctrine()
		    ->getRepository('App\Entity\Product')
		    ->createQueryBuilder('p')
		    ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $this->json($products);
    }

}
