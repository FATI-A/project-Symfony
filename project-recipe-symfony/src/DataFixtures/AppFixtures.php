<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture; //we want the data to be the same between test runs to make the tests pass.
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    //  @var Generator for generating des fake data
    private Generator $faker;


    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
       
    }


    public function load(ObjectManager $manager): void
    {
        //Ingredients
        $ingredients = [];
        for ($i = 0; $i < 50; $i++) {
            $ingredient = new Ingredient;
            $ingredient->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100));
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        //Recipes
        for ($j = 0; $j < 25; $j++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(0, 1440) : 0)
                ->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(0, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(0, 5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(0, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false);
            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $manager->persist($recipe);
        }
        //Users 

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



        $manager->flush();
    }
}
