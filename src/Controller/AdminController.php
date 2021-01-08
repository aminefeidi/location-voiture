<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\Utilisateur;
use App\Entity\Voiture;
use App\Repository\AgenceRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\VoitureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/agences", name="admin_agence")
     */
    public function agence(AgenceRepository $repository): Response
    {
        $agences = $repository->findAll();

        return $this->render('admin/agence.html.twig', [
            'controller_name' => 'AdminController',
            'agences' => $agences
        ]);
    }

    /**
     * @Route("/admin/agences/ajouter", name="admin_agence_ajout")
     */
    public function ajouterAgence(Request $request, AgenceRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $agence = new Agence();

        $form = $this->createFormBuilder($agence)
            ->add('nom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('tel', TextType::class, ['label' => 'Telephone'])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agence = $form->getData();
            $entityManager->persist($agence);
            $entityManager->flush();
            return $this->redirectToRoute('admin_agence');
        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/agences/{id}/modifier", name="admin_agence_modif")
     */
    public function modifierAgence(string $id, Request $request, AgenceRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $agence = $repository->find($id);

        $form = $this->createFormBuilder($agence)
            ->add('nom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('tel', TextType::class, ['label' => 'Telephone'])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_agence');
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/agences/{id}/supprimer", name="admin_agence_supp")
     */
    public function supprimerAgence(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Agence::class);
        $agence = $repository->find($id);
        $entityManager->remove($agence);
        $entityManager->flush();
        return $this->redirectToRoute("admin_agence");
    }

    /**
     * @Route("/admin/agents", name="admin_agent")
     */
    public function agent(UtilisateurRepository $repository): Response
    {
        $agents = $repository->findBy(['type' => 1]);

        return $this->render('admin/agent.html.twig', [
            'agents' => $agents,
        ]);
    }

    /**
     * @Route("/admin/agents/ajouter", name="admin_agent_ajout")
     */
    public function ajouterAgent(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $agent = new Utilisateur();
        $agent->setType(1);

        $form = $this->createFormBuilder($agent)
            ->add('email', EmailType::class)
            ->add('motDePasse', TextType::class)
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agent = $form->getData();
            $entityManager->persist($agent);
            $entityManager->flush();
            return $this->redirectToRoute('admin_agent');
        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/agents/{id}/modifier", name="admin_agent_modif")
     */
    public function modifierAgent(string $id, Request $request, UtilisateurRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $agent = $repository->find($id);

        $form = $this->createFormBuilder($agent)
            ->add('email', EmailType::class)
            ->add('motDePasse', TextType::class)
            ->add('sauvegarder', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agent = $form->getData();
            $entityManager->flush();
            return $this->redirectToRoute('admin_agent');
        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/agents/{id}/supprimer", name="admin_agent_supp")
     */
    public function supprimerAgent(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $agent = $repository->find($id);
        $entityManager->remove($agent);
        $entityManager->flush();
        return $this->redirectToRoute("admin_agent");
    }

    /**
     * @Route("/admin/voitures", name="admin_voiture")
     */
    public function voiture(VoitureRepository $repository): Response
    {
        $voitures = $repository->findAll();

        return $this->render('admin/voiture.html.twig', [
            'voitures' => $voitures,
        ]);
    }

    /**
     * @Route("/admin/voitures/ajouter", name="admin_voiture_ajout")
     */
    public function ajouterVoiture(Request $request, AgenceRepository $agenceRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $voiture = new Voiture();
        $voiture->setDisponibilite(true);

        $agences = $agenceRepository->findAll();
        $choix = array();

        foreach ($agences as $agence) {
            $choix[$agence->getNom()] = $agence;
        }

        $form = $this->createFormBuilder($voiture)
            ->add('matricule', TextType::class)
            ->add('marque', TextType::class)
            ->add('couleur', TextType::class)
            ->add('carburant', TextType::class)
            ->add('nbrPlace', TextType::class, ['label' => 'N° de places'])
            ->add('description', TextType::class)
            ->add('dateMiseEnCirculation', DateType::class)
            ->add('agence', ChoiceType::class,[
                'choices' => $choix
            ])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $voiture = $form->getData();
            $entityManager->persist($voiture);
            $entityManager->flush();
            return $this->redirectToRoute('admin_voiture');
        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/voitures/{id}/modifier", name="admin_voiture_modif")
     */
    public function modifierVoiture(string $id, Request $request, VoitureRepository $repository, AgenceRepository $agenceRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $voiture = $repository->find($id);

        $agences = $agenceRepository->findAll();
        $choix = array();

        foreach ($agences as $agence) {
            $choix[$agence->getNom()] = $agence;
        }

        $form = $this->createFormBuilder($voiture)
            ->add('matricule', TextType::class)
            ->add('marque', TextType::class)
            ->add('couleur', TextType::class)
            ->add('carburant', TextType::class)
            ->add('nbrPlace', TextType::class, ['label' => 'N° de places'])
            ->add('description', TextType::class)
            ->add('dateMiseEnCirculation', DateType::class)
            ->add('agence', ChoiceType::class,[
                'choices' => $choix
            ])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_voiture');
        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/voitures/{id}/supprimer", name="admin_voiture_supp")
     */
    public function supprimerVoiture(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Voiture::class);
        $voiture = $repository->find($id);
        $entityManager->remove($voiture);
        $entityManager->flush();
        return $this->redirectToRoute("admin_voiture");
    }
}
