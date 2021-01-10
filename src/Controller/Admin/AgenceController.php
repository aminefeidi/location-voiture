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

class AgenceController extends AbstractController
{
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
}
