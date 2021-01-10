<?php

namespace App\Controller\Admin;

use App\Entity\Agence;
use App\Entity\Utilisateur;
use App\Entity\Voiture;
use App\Repository\AgenceRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\VoitureRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgentController extends AbstractController
{

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
      ->add('agence', EntityType::class, [
        'class' => Agence::class,
        'choice_label' => 'nom'
      ])
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
      ->add('agence', EntityType::class, [
        'class' => Agence::class,
        'choice_label' => 'nom'
      ])
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
}
