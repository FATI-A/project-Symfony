<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{

    /**
     * this controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe.index', methods: ["GET"])]
    public function index(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $recipes = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * this controller show  a form which create recipe 
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/creation', name: 'recipe.new', methods: ["GET", "POST"])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe, [
            'submit_label' => $recipe->getId() ? 'Mettre à jour ma recette' : 'Créer ma recette'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'votre recette a été créée avec succes '
            );

            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }




    /**
     * this controller allow us to update recipe
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ["GET", "POST"])]
    public function edit(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        // Si $recipe->getId() est null, alors c'est une création, sinon c'est une mise à jour
        $form = $this->createForm(RecipeType::class, $recipe, [
            'submit_label' => $recipe->getId() ? 'Mettre à jour ma recette' : 'Créer ma recette'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'votre recette a été modifié avec succes '
            );
            return $this->redirectToRoute('recipe.index');
        }
        return $this->Render(
            'pages/recipe/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );;
    }

 
    /**
     * this controller allow us to delete recipe
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/suppression/{id}', 'recipe.delete', methods: ["GET"])]
    public function delete(
        Recipe $recipe,
        EntityManagerInterface $manager
    ): Response {
        if (!$recipe) {

            $this->addFlash(
                'success',
                'votre recette n\'a pas été trouvée '
            );
            return $this->redirectToRoute('recipe.index');
        }
        $manager->remove($recipe);
        $manager->flush();


        $this->addFlash(
            'success',
            'votre recette a été supprimée avec succes '
        );

        return $this->redirectToRoute('recipe.index');
    }
}
