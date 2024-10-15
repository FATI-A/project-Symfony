<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/articles', name: 'api_articles', methods: ['GET'])]

    public function index(): Response
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        return $this->json($articles);
    }

    #[Route('/api/articles/{id}', name: 'api_article_show', methods: ['GET'])]

    public function show(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($article);
    }

    #[Route('/api/articles', name: 'api_article_create', methods: ['POST'])]

    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $this->json($article, Response::HTTP_CREATED);
    }

    #[Route('/api/articles/{id}', name: 'api_article_update', methods: ['PUT'])]

    public function update(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $article->setTitle($data['title']);
        $article->setContent($data['content']);

        $this->entityManager->flush();

        return $this->json($article);
    }


    #[Route('/api/articles/{id}', name: 'api_article_delete', methods: ['DELETE'])]

    public function delete(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->json(['success' => 'Article deleted'], Response::HTTP_NO_CONTENT);
    }
}
