<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * this method allows us to find public recipe based on number of recipes
     *
     * @param integer $nbRecipes
     * @return array
     */
    public function findPublicRecipe(?int $nbRecipes): array
    {

        $queryBuilder = $this->createQueryBuilder('r')
            ->where('r.isPublic = 1')
            ->orderBy('r.createdAt', 'DESC');

        if (!$nbRecipes !== 0 || $nbRecipes !== null) {
            $queryBuilder->setMaxResults($nbRecipes);
        }
        return $queryBuilder->getQuery()
            ->getResult();
    }
}
