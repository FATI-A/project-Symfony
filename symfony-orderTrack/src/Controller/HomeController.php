<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name:'home.index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('pages/home.html.twig', [
           'articles'=> $articleRepository->findAll(),
        ]);
    }


    #[Route('/article/{id}', 'article.show', methods: ["GET", "POST"])]
    public function show(
        int $id,
        EntityManagerInterface $manager
    ): Response {
        $article = $manager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException("L'article avec l'id $id n'existe pas.");
        }
        return $this->render('pages/article/show.html.twig', [
            'article' => $article,
        ]);
    }



    
}
