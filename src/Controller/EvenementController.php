<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Form\EvenementType;
use App\Form\InscriptionType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\FileUploader;

final class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(EvenementRepository $repo, Request $request): Response
    {
        $session = $request->getSession();
        $ids = $session->get('historique_evenements', []);
        $historique = array_filter(array_map(fn($id) => $repo->find($id), $ids));

        return $this->render('evenement/home.html.twig', [
            'evenements' => $repo->findUpcoming(6),
            'historique' => $historique,
        ]);
    }

    #[Route('/evenements', name: 'app_evenement_index')]
    public function index(Request $request, EvenementRepository $repo, PaginatorInterface $paginator): Response
    {
        $categorie = $request->query->get('categorie');
        $ville     = $request->query->get('ville');
        $q         = $request->query->get('q');
        $tag       = $request->query->get('tag');

        $query = $repo->findByFiltersQuery($categorie, $ville, $q, $tag);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('evenement/index.html.twig', [
            'pagination' => $pagination,
            'filtres'    => compact('categorie', 'ville', 'q', 'tag'),
        ]);
    }

    #[Route('/evenements/nouveau', name: 'app_evenement_new')]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANISATEUR');

        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $evenement->setImageName($uploader->upload($imageFile));
            }
            $evenement->setOrganisateur($this->getUser());
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('app_evenement_index');
        }

        return $this->render('evenement/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/evenements/{id}', name: 'app_evenement_show')]
    public function show(Evenement $evenement, Request $request, PaginatorInterface $paginator): Response
    {
        $session = $request->getSession();
        $historique = $session->get('historique_evenements', []);

        if (!in_array($evenement->getId(), $historique)) {
            array_unshift($historique, $evenement->getId());
            $historique = array_slice($historique, 0, 5);
            $session->set('historique_evenements', $historique);
        }

        $pagination = $paginator->paginate(
            $evenement->getInscriptions(),
            $request->query->getInt('page', 1),
            10
        );

        // Vérifie si l'utilisateur connecté est déjà inscrit
        $dejaInscrit = false;
        if ($this->getUser()) {
            $dejaInscrit = $evenement->getInscriptions()->exists(
                fn($key, $inscription) => $inscription->getParticipant() === $this->getUser()
            );
        }

        return $this->render('evenement/show.html.twig', [
            'evenement'    => $evenement,
            'inscriptions' => $pagination,
            'dejaInscrit'  => $dejaInscrit,
        ]);
    }

    #[Route('/evenements/{id}/modifier', name: 'app_evenement_edit')]
    public function edit(Evenement $evenement, Request $request, EntityManagerInterface $em, FileUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('EVENEMENT_EDIT', $evenement);

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                if ($evenement->getImageName()) {
                    $uploader->remove($evenement->getImageName());
                }
                $evenement->setImageName($uploader->upload($imageFile));
            }
            $em->flush();
            $this->addFlash('success', 'Événement modifié.');
            return $this->redirectToRoute('app_evenement_index');
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/evenements/{id}/supprimer', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em, FileUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('EVENEMENT_DELETE', $evenement);

        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            if ($evenement->getImageName()) {
                $uploader->remove($evenement->getImageName());
            }
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/evenements/{id}/inscription', name: 'app_evenement_inscription')]
    public function inscription(Evenement $evenement, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Bloquer si annulé ou complet
        if (in_array($evenement->getStatut(), ['annule', 'complet'])) {
            $this->addFlash('danger', 'Impossible de s\'inscrire : cet événement est ' . $evenement->getStatut() . '.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        // Bloquer la double inscription
        $dejaInscrit = $em->getRepository(Inscription::class)->findOneBy([
            'evenement'   => $evenement,
            'participant' => $this->getUser(),
        ]);

        if ($dejaInscrit) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        $inscription = new Inscription();
        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setEvenement($evenement);
            $inscription->setParticipant($this->getUser());
            $inscription->setStatut('en_attente');
            $em->persist($inscription);
            $em->flush();
            $this->addFlash('success', 'Inscription enregistrée !');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/inscription.html.twig', [
            'evenement' => $evenement,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/historique', name: 'app_evenement_historique')]
    public function historique(Request $request, EvenementRepository $repo): Response
    {
        $ids = $request->getSession()->get('historique_evenements', []);
        $evenements = array_filter(array_map(fn($id) => $repo->find($id), $ids));

        return $this->render('evenement/historique.html.twig', [
            'evenements' => $evenements,
        ]);
    }
}