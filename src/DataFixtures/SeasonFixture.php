<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Season;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;


class SeasonFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker=Faker\Factory::create('en_US');
        for ($i=0;$i<=30;$i++)
        {
            $season=new Season();
            $season->setNumber($faker->numberBetween(1,5));
            $season->setYear($faker->year());
            $season->setDescription($faker->text());
            $season->setProgram($this->getReference('program_'.rand(0,5),$season));
            $this->addReference('season_' . $i, $season);
            $manager->persist($season);
        }

        $manager->flush();
    }


    public function getDependencies()  

    {

        return [ProgramFixtures::class];  

    }
}
