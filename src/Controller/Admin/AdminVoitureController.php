<?php

namespace App\Controller\Admin;

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

class AdminVoitureController extends AbstractController
{
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
      ->add('agence', ChoiceType::class, [
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
      ->add('agence', ChoiceType::class, [
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
