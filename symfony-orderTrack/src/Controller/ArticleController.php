<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{

    /**
     * This controller display all articles
     *
     * @param ArticleRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/article', name: 'article.index', methods: ['GET'])]
    public function index(
        ArticleRepository $repository,
        PaginatorInterface  $paginator,
        Request $request
    ): Response {
        $articles = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * this controller show  a form which create an article
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/article/nouveau', 'article.new', methods: ["GET", "POST"])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article, [
            'submit_label' => $article->getId() ? 'Mettre à jour ma recette' : 'Créer ma recette'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $manager->persist($article);
            $manager->flush();

            $this->addFlash(
                'success',
                'votre article a été créé avec succes '
            );
            return $this->redirectToRoute('article.index');
        }
        return $this->render('pages/article/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


 
    #[Route('/article/suppression/{id}', 'article.delete', methods: ["GET"])]
    public function delete(
        EntityManagerInterface $manager, 
        Article $article ): Response
    {
        if (!$article) {

            $this->addFlash(
                'success',
                'votre article n\'a pas été trouvé '
            );
            return $this->redirectToRoute('article.index');
        }
        $manager->remove($article);
        $manager->flush();


        $this->addFlash(
            'success',
            'votre article a été supprimé avec succes '
        );

        return $this->redirectToRoute('article.index');
    }
}
