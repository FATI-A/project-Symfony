<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeArticle;
use App\Form\CommandeType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\TextUI\Command;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Psr\Log\LoggerInterface;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'commande.index')]
    public function index(): Response
    {
        return $this->render('pages/commande/index.html.twig', []);
    }

    #[Route('/commande/nouveau', 'commande.new', methods: ["GET", "POST"])]
    public function new(Request $request, EntityManagerInterface $Manager, LoggerInterface $logger)
    {

        $commande = new Commande();
        $commande->setStatut('En attente');
        $commande->setUser($this->getUser());


        $form = $this->createForm(CommandeType::class, $commande, [
            'submit_label' => $commande->getId() ? 'Mettre à jour ma commande' : 'Créer ma commande'
        ]);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $articles = $form->get('article')->getData();

            // Pour chaque article sélectionné, créez un CommandeArticle
            foreach ($articles as $index => $article) {
                $commandeArticle = new CommandeArticle();
                $commandeArticle->setCommande($commande);
                $commandeArticle->setArticle($article);
                $commandeArticle->setQuantity($quantities[$index] ?? 1);

                $Manager->persist($commandeArticle);
            }

            $Manager->persist($commande);
            $Manager->flush();

            $this->addFlash('success', 'Commande créée avec succès!');
            return $this->redirectToRoute('article.index');
        } else {
            foreach ($form->getErrors(true) as $error) {
                dump($error->getMessage());
            }
        }

        return $this->render('pages/commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


/**
 * this controller allows us to get commande list by user
 *
 * @param EntityManagerInterface $Manager
 * @return void
 */
    #[Route('/commande/list', 'commande.list', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function list(EntityManagerInterface $Manager)
    {
 
        $user = $this->getUser();
        $commandes = $Manager->getRepository(Commande::class)->findBy(['user' => $user]);

        return $this->render('Pages/commande/list.html.twig', [
            'commandes' => $commandes,
        ]);
    }



    #[Security("is_granted('ROLE_USER') and user === commande.getUser()")]
    #[Route('/commande/suppression/{id}', 'commande.delete', methods: ["GET","POST"])]
    /**
     * this controller allows us to delete a command
     *
     * @param Commande $commande
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === commande.getUser()")]
    #[Route('/commande/suppression/{id}', 'commande.delete', methods: ["GET", "POST"])]
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


}
