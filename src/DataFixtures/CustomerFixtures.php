<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Customer;
use Ramsey\Uuid\Uuid;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $customer = new Customer();
        $customer->setUuid(Uuid::uuid1()->toString());
        $customer->setFirstName('Test');
        $customer->setLastName('User');
        $customer->setDateOfBirth(new \DateTime());
        $customer->setStatus('new');

        $manager->persist($customer);
        $manager->flush();
    }
}
