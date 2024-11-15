<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeArticle;
use App\Repository\ArticleRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class CommandeController extends AbstractController
{
    /**
     * this controller allows us to show all the commands
     *
     * @param CommandeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/commande', name: 'commande.index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        CommandeRepository $repository,
        PaginatorInterface  $paginator,
        Request $request
    ): Response {
        $commandes = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/commande/index.html.twig', [
            'commandes' => $commandes
        ]);
    }



    /**
     * this controller allows us to create a new command
     *
     * @param Request $request
     * @param EntityManagerInterface $Manager
     * @param ArticleRepository $articleRepository
     * @return void
     */
    #[Route('/commande/nouveau-clasique', 'commande.new_classique', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function new_classique(
        Request $request,
        EntityManagerInterface $Manager,
        ArticleRepository $articleRepository
    ) {
        $commande = new Commande();
        $commande->setStatut('En attente');
        $commande->setUser($this->getUser());
        $articles = $articleRepository->findAll();
        if ($request->isMethod('POST')) {
            $statut = $request->request->get('statut');
            $dateCommande = $request->get('date_commande');
            $articleIds = $request->get('article_ids', []);
            $quantities = $request->get('quantities', []);

            if (!empty($articleIds) && is_array($articleIds) && is_array($quantities)) {
                foreach ($articleIds as $articleId) {
                    $article = $articleRepository->find($articleId);

                    if ($article && $quantities[$articleId] > 0) {

                        $quantityRequested = $quantities[$articleId];
                        if ($article->getStock() >= $quantityRequested) {
                            $commandeArticle = new CommandeArticle();
                            $commandeArticle->setArticle($article);
                            $commandeArticle->setQuantity($quantityRequested);
                            $commandeArticle->setCommande($commande);

                            $commande->addCommandeArticle($commandeArticle);
                          
                            $article->setStock($article->getStock() - $quantityRequested);
                            $Manager->persist($article);  
                            $Manager->persist($commandeArticle);
                        } else {
                            $this->addFlash('error', "Stock insuffisant pour l'article: {$article->getName()}.");
                            return $this->redirectToRoute('commande.new_classique');
                        }
                    }
                }
            } else {
                
                $this->addFlash('error', 'Erreur dans les données du formulaire : articles ou quantités invalides.');
                return $this->redirectToRoute('commande.new_classique');
            }


            $commande->setDate(new \DateTime($dateCommande));
            $commande->setStatut($statut);

            $Manager->persist($commande);
            $Manager->flush();


            $this->addFlash('success', 'Commande créée avec succès !');


            return $this->redirectToRoute('commande.list');
        }

        return $this->render('pages/commande/new_classique.html.twig', [
            'articles' => $articles,
        ]);
    }


    // #[Route('/commande/nouveau', 'commande.new', methods: ["GET", "POST"])]
    // public function new(
    //     Request $request,
    //      EntityManagerInterface $Manager, 
    //       LoggerInterface $logger)
    // {

    //     $commande = new Commande();
    //     $commande->setStatut('En attente');
    //     $commande->setUser($this->getUser());;

    //     $form = $this->createForm(CommandeType::class, $commande, [
    //         'submit_label' => $commande->getId() ? 'Mettre à jour ma commande' : 'Créer ma commande'
    //     ]);

    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $commande = $form->getData();
    //         $logger->info('Données de la commande:', ['commande' => $commande]);
    //         $commandeArticles = $commande->getCommandeArticles();

    //         foreach ($commandeArticles as $commandeArticle) {
    //             $commandeArticle->setCommande($commande);
    //             $Manager->persist($commandeArticle);
    //         }

    //         $Manager->persist($commande);
    //         $Manager->flush();
    //         $this->addFlash('success', 'Commande créée avec succès !');


    //         return $this->redirectToRoute('commande.list');
    //     }

    //     return $this->render('pages/commande/new.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }


    /**
     * this contoller allows us to update status of commande
     *
     * @param integer $id
     * @param Request $request
     * @param EntityManagerInterface $Manager
     * @param Commande $commande
     * @param CommandeRepository $commandeRepository
     * @return Response
     */
    #[Route('/commande/update-status/{id}', 'commande.update_status', methods: ["GET", "POST"])]
    #[Security("is_granted('ROLE_USER') and user === commande.getUser()")]
    public function updateStatus(
        int $id,
        Request $request,
        EntityManagerInterface $Manager,
        Commande $commande,
        CommandeRepository $commandeRepository
    ): Response {

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('commande.list');
        }

        if ($commande->getUser() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à modifier cette commande.');
        }

        $statuts = [
            'En attente' => 'En attente',
            'En cours' => 'En cours',
            'Validée' => 'livrée',
        ];

        if ($request->isMethod('POST')) {
            $statut = $request->request->get('statut');

            if (!array_key_exists($statut, $statuts)) {
                $this->addFlash('error', 'Statut invalide.');
                return $this->redirectToRoute('commande.update_status', ['id' => $id]);
            }

            $commande->setStatut($statut);
            $Manager->flush();
            $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
            return $this->redirectToRoute('commande.list');
        }

        return $this->render('pages/commande/update_status.html.twig', [
            'commande' => $commande,
            'statuts' => $statuts
        ]);
    }


    /**
     * this controller allows us to get commande list 
     *
     * @param CommandeRepository $commandeRepository
     * @return void
     */
    #[Route('/commande/list', 'commande.list', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function list(CommandeRepository $commandeRepository)
    {

        $user = $this->getUser();
        $commandes = $commandeRepository->findBy(['user' => $user]);

        return $this->render('Pages/commande/list.html.twig', [
            'commandes' => $commandes,
        ]);
    }



    #[Route('/commande/show/{id}', 'commande.show', methods: ["GET", "POST"])]
    #[Security("is_granted('ROLE_USER') and user === commande.getUser() || is_granted('ROLE_USER') ")]
    public function show(int $id, CommandeRepository $commandeRepository, Commande $commande): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        $totalPrice = 0;
        foreach ($commande->getCommandeArticles() as $commandeArticle) {
            $totalPrice += $commandeArticle->getQuantity() * $commandeArticle->getArticle()->getPrice();
        }

        return $this->render('pages/commande/show.html.twig', [
            'commande' => $commande,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * this controller allows us to delete a command
     *
     * @param Commande $commande
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/commande/suppression/{id}', 'commande.delete', methods: ["GET", "POST"])]
    #[Security("is_granted('ROLE_USER') and user === commande.getUser()")]
    public function delete(
        Commande $commande,
        EntityManagerInterface $manager
    ): Response {
        if (!$commande) {

            $this->addFlash(
                'success',
                'votre recette n\'a pas été trouvée '
            );
            return $this->redirectToRoute('commande.list');
        }
        $manager->remove($commande);
        $manager->flush();


        $this->addFlash(
            'success',
            'votre recette a été supprimée avec succes '
        );

        return $this->redirectToRoute('commande.list');
    }



    /**
     * this controller allow us to show commande's details
     *
     * @param integer $id
     * @param CommandeRepository $commandeRepository
     * @param Commande $commande
     * @return Response
     */
}
