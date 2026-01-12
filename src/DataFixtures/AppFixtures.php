<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Provider;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void {
        $types = ['hotel', 'crucero', 'esqui', 'parque'];
        for ($i = 0; $i < 20; $i++) {
            $p = new Provider();
            $p->setName("Proveedor $i")->setEmail("prov$i@test.com")
            ->setPhone("60000000$i")->setType($types[array_rand($types)])
            ->setActive(true);
            $manager->persist($p);
        }
        $manager->flush();
    }
}
