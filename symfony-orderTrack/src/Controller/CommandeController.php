<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\CommandeArticle;
use App\Form\CommandeType;
use App\Repository\ArticleRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\TextUI\Command;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
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




    #[Route('/commande/nouveau-clasique', 'commande.new_classique', methods: ["GET", "POST"])]
    public function new_classique(
        Request $request,
        EntityManagerInterface $Manager,
        LoggerInterface $logger,
        ArticleRepository $articleRepository
    ) {
        $commande = new Commande();

        // Par défaut, une commande est en statut "En attente"
        $commande->setStatut('En attente');
        $commande->setUser($this->getUser());  // Associer l'utilisateur connecté à la commande

        // Récupérer tous les articles disponibles pour être affichés dans le formulaire
        $articles = $Manager->getRepository(Article::class)->findAll();

        // Traiter la soumission du formulaire
        if ($request->isMethod('POST')) {
            // Récupérer les données envoyées par le formulaire
            $statut = $request->request->get('statut'); // Récupérer le statut de la commande
            $articleIds = $request->request->get('article_ids', []);  // Les articles sélectionnés
            $quantities = $request->request->get('quantities', []);  // Les quantités pour chaque article

            // Mettre à jour le statut de la commande
            $commande->setStatut($statut);
            $articleIds = $request->request->get('article_ids', []); // Récupérer les IDs des articles sélectionnés
            $quantities = $request->request->get('quantities', []); // Récupérer les quantités associées
            $logger->info('test_info' . $articleIds);
            // Vérification que les IDs et les quantités existent
            if (is_array($articleIds) && $quantities) {
                foreach ($articleIds as $index => $articleId) {
                    // Trouver l'article correspondant par son ID
                    $article = $articleRepository->find($articleId);

                    // Vérifier si l'article existe et si la quantité est valide
                    if ($article && isset($quantities[$index]) && $quantities[$index] > 0) {
                        // Créer un nouvel objet CommandeArticle
                        $commandeArticle = new CommandeArticle();
                        $commandeArticle->setArticle($article);
                        $commandeArticle->setQuantity($quantities[$index]);
                        $commandeArticle->setCommande($commande);

                        // Ajouter ce CommandeArticle à la commande
                        $commande->addCommandeArticle($commandeArticle);

                        // Persister l'objet CommandeArticle dans la base de données
                        $Manager->persist($commandeArticle);
                    }
                }
            }

            // Sauvegarder la commande dans la base de données
            $Manager->persist($commande);
            $Manager->flush();  // Enregistrer toutes les données

            // Afficher un message de succès
            $this->addFlash('success', 'Commande créée avec succès !');

            // Rediriger vers la liste des commandes
            return $this->redirectToRoute('commande.list');
        }

        return $this->render('pages/commande/new_classique.html.twig', [
            'articles' => $articles,  // Passer les articles à la vue
        ]);
    }


    #[Route('/commande/nouveau', 'commande.new', methods: ["GET", "POST"])]
    public function new(
        Request $request,
         EntityManagerInterface $Manager, 
          LoggerInterface $logger)
    {

        $commande = new Commande();
        $commande->setStatut('En attente');
        $commande->setUser($this->getUser());;

        $form = $this->createForm(CommandeType::class, $commande, [
            'submit_label' => $commande->getId() ? 'Mettre à jour ma commande' : 'Créer ma commande'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commande = $form->getData();
            $logger->info('Données de la commande:', ['commande' => $commande]);
            $commandeArticles = $commande->getCommandeArticles();

            foreach ($commandeArticles as $commandeArticle) {
                $commandeArticle->setCommande($commande);
                $Manager->persist($commandeArticle);
            }

            $Manager->persist($commande);
            $Manager->flush();
            $this->addFlash('success', 'Commande créée avec succès !');


            return $this->redirectToRoute('commande.list');
        }

        return $this->render('pages/commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


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
