<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
    #[IsGranted('ROLE_USER')]
    public function index(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }


    /**
     * this controller allows to share recipe with community
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/communaute', 'recipe.community', methods: ['GET'])]
    public function indexPublic(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {

        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/community.html.twig', [
            'recipes' => $recipes
        ]);
    }


 /**
  * This controller allow us to create a new recipe
  *
  * @param Request $request
  * @param EntityManagerInterface $manager
  * @return Response
  */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe= new Recipe();
        $form= $this-> createForm(RecipeType::class,$recipe,[
            'submit_label' => $recipe->getId() ? 'Mettre à jour ma recette' : 'Créer ma recette'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe= $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été créé avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
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
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
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
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
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



    /**
     * this controller allows us to see a recipe if this one is public
     *
     * @param Recipe $recipe
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and (recipe.getIsPublic() === true || user === recipe.getUser())")]
    #[Route('/recette/{id}', 'recipe.show', methods: ["GET", "POST"])]
    public function show(
        Recipe $recipe,
        Request $request,
        MarkRepository $markRepository,
        EntityManagerInterface $manager
    ): Response {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mark = $form->getData();
            $mark->setUser($this->getUser());
            $mark->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark) {
                $manager->persist($mark);
            } else {

                $existingMark->setMark($form->getData()->getMark());
            }
            $manager->flush();

            $this->addFlash(
                'success',
                "vous avez bien noté la recette "
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

}
