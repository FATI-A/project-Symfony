<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredienType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; // paginator for having 10 items per page and have à scroller/ pagination
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class IngredientController extends AbstractController
{
    /**
     * This controller display all ingredient

     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */

    #[Route('/ingredient', name: 'ingredient.index', methods: ["GET"])]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * this controller show  a form which create un ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ["GET", "POST"])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredienType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            /**dd($ingredient);*/
            /**persist et flush we can say it's the same logique as commit and push */

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'votre ingredient a été créé avec succes '
            );
            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * this controller allow us to update an ingredient
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ["GET", "POST"])]
    public function edit(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $form = $this->createForm(IngredienType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            /**dd($ingredient);*/
            /**persist et flush we can say it's the same logique as commit and push */

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'votre ingredient a été modifié avec succes '
            );
            return $this->redirectToRoute('ingredient.index');
        }
        return $this->Render(
            'pages/ingredient/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );;
    }


    /**
     * this controller allow us to delete an ingredient
     *
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @return Response
     */
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ["GET"])]
    public function delete(EntityManagerInterface $manager, Ingredient $ingredient): Response
    {
        if (!$ingredient) {

            $this->addFlash(
                'success',
                'votre ingredient n\'a pas été trouvé '
            );
            return $this->redirectToRoute('ingredient.index');
        }
        $manager->remove($ingredient);
        $manager->flush();


        $this->addFlash(
            'success',
            'votre ingredient a été supprimé avec succes '
        );

        return $this->redirectToRoute('ingredient.index');
    }
}
