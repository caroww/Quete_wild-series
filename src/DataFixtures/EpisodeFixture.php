<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Episode;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;
use App\Service\Slugify;

class EpisodeFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $faker=Faker\Factory::create('en_US');
        for ($i=0;$i<=200;$i++)
        {
            $episode=new Episode();
            $slugify = new Slugify();
            $episode->setNumber($faker->numberBetween(1,12));
            $episode->setTitle($faker->sentence());
            $episode->setSynopsis($faker->text());
            $slug = $slugify->generate($episode->getTitle());
            $episode->setSlug($slug);
            $episode->setSeason($this->getReference('season_'.rand(1,5)));
            $manager->persist($episode);
        }

        $manager->flush();
    }

    public function getDependencies()  

    {

        return [SeasonFixture::class];  

    }
}
