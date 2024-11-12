<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    //  @var Generator for generating des fake data
    private Generator $faker;


    public function __construct()
    {
        $this->faker = Factory::create('fr','FR');
    }
    public function load(ObjectManager $manager): void
    {

        // Articles
        for ($i = 0; $i < 50; $i++) {
            $article = new Article();
            $article->setName($this->faker->name(100))
                ->setDescription($this->faker->text(300))
                ->setPrice((mt_rand(0, 1000) ))
                ->setStock((mt_rand(0, 1) == 1 ? mt_rand(0, 1000) : 0));
            $manager->persist($article);
        }

        $manager->flush();
      
    }
}
