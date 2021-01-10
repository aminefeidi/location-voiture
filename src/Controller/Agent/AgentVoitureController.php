<?php

namespace App\Controller\Agent;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\Facture;
use App\Repository\ClientRepository;
use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\VoitureRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgentVoitureController extends AbstractController
{
  /**
   * @Route("/agent/voitures", name="agent_voiture")
   */
  public function voiture(VoitureRepository $repository): Response
  {
    $agence = $this->getUser()->getAgence();
    $voitures = $repository->findBy(['agence' => $agence]);

    return $this->render('agent/voiture.html.twig', [
      'voitures' => $voitures,
    ]);
  }

  /**
   * @Route("/agent/voitures/{id}/louer", name="agent_voiture_louer")
   */
  public function louerVoiture(string $id, VoitureRepository $repository): Response
  {
    $entityManager = $this->getDoctrine()->getManager();
    $voiture = $repository->find($id);
    $voiture->setDisponibilite(false);
    $entityManager->flush();

    return $this->redirectToRoute('agent_voiture');
  }

  /**
   * @Route("/agent/voitures/{id}/rendre", name="agent_voiture_rendre")
   */
  public function rendreVoiture(string $id, VoitureRepository $repository): Response
  {
    $entityManager = $this->getDoctrine()->getManager();
    $voiture = $repository->find($id);
    $voiture->setDisponibilite(true);
    $entityManager->flush();

    return $this->redirectToRoute('agent_voiture');
  }
}
