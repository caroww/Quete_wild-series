<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Actor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    const ACTORS = [
        'Andrew Lincoln',        
        'Norman Reedus',
        'Lauren Cohan',
        'Danai Gurira', 
    ];

    public function load(ObjectManager $manager)
    {
        $i=0;
       
        foreach (self::ACTORS as $name => $data) {
            $actor=new Actor();
            $actor->setName($data);
            if ($data=="Andrew Lincoln"){
                $actor->addProgram($this->getReference('program_'.(5),$actor));
            }
            $actor->addProgram($this->getReference('program_'.(0),$actor));
            $this->setReference('actor_' . $i, $actor);
            $manager->persist($actor);
            $i++;
        }
        $faker=Faker\Factory::create('en_US');
        for ($i=0;$i<=50;$i++)
        {
            $actor=new Actor();
            $actor->setName($faker->name());
            $actor->addProgram($this->getReference('program_'.rand(0,5),$actor));
            $manager->persist($actor);
        }
        $manager->flush();
    }

    public function getDependencies()  

    {

        return [ProgramFixtures::class];  

    }
}
