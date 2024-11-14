<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\CommandeArticle;
use App\Entity\User;
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
          //Users
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            $users[] = $user;
            $manager->persist($user);
        }




        // Articles
        $articles = [];
        for ($i = 0; $i < 50; $i++) {
            $article = new Article();
            $article->setName($this->faker->name(100))
                ->setDescription($this->faker->text(300))
                ->setPrice((mt_rand(0, 1000) ))
                ->setStock((mt_rand(0, 1) == 1 ? mt_rand(0, 1000) : 0));
                $articles[]=$article;
            $manager->persist($article);
        }

        //Commandes

       for ($i = 0; $i < 10; $i++) {
            $commande = new Commande();
            $commande->setDate(new \DateTimeImmutable()) 
                     ->setStatut('En attente')
                      ->setUser($users[mt_rand(0, count($users) - 1)]);
            foreach ($articles as $article) {
                $quantity = mt_rand(1, 10); 
                $CommandeArticle = new CommandeArticle();
                $CommandeArticle->setArticle($article)
                         ->setCommande($commande)
                         ->setQuantity($quantity);
                $manager->persist($CommandeArticle); 
            }
            $commande->addCommandeArticle($CommandeArticle) ;

            $manager->persist($commande);
        }

        $manager->flush();
      
    }
}
