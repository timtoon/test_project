<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Product;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
    	$status = ['pending','new'];
    
        $customer = $manager->getRepository('App\Entity\Customer')->find(1);

        foreach($status as $s) {
	        for ($i = 0; $i < 5; $i++) {
	            $product = new Product();
	            $product->setIssn(mt_rand(100, 1000));
	            $product->setName($s.' product '.$i);
	            $product->setStatus($s);
	            $product->setCustomer($customer);
	            $manager->persist($product);
	        }
        }

        $manager->flush();
    }
}
