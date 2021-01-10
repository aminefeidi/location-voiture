<?php

namespace App\Controller\Agent;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\Facture;
use App\Repository\ClientRepository;
use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\VoitureRepository;
use DateInterval;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContratController extends AbstractController
{
    /**
     * @Route("/agent/contrats", name="agent_contrat")
     */
    public function contrat(ContratRepository $repository): Response
    {
        $contrats = $repository->findAll();

        return $this->render('agent/contrat.html.twig', [
            'contrats' => $contrats,
        ]);
    }

    /**
     * @Route("/agent/contrats/ajouter", name="agent_contrat_ajout")
     */
    public function ajouterContrat(Request $request, ClientRepository $clientRepository, VoitureRepository $voitureRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $contrat = new Contrat();
        $today = new DateTime();
        $contrat->setDateDep(new DateTime());
        $contrat->setDateRet($today->add(new DateInterval('P1D')));

        $clients = $clientRepository->findAll();
        $choixClient = array();

        foreach ($clients as $client) {
            $choixClient[$client->getId() . ' - ' . $client->getNom()] = $client;
        }

        $voitures = $voitureRepository->findAll();
        $choixVoiture = array();

        foreach ($voitures as $voiture) {
            if ($voiture->getDisponibilite()) {
                $choixVoiture[$voiture->getId() . ' - ' . $voiture->getMarque()] = $voiture;
            }
        }

        $form = $this->createFormBuilder($contrat)
            ->add('client', ChoiceType::class, [
                'choices' => $choixClient
            ])
            ->add('voiture', ChoiceType::class, [
                'choices' => $choixVoiture
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'type 1' => 1,
                    'type 2' => 2,
                    'type 3' => 3,
                ]
            ])
            ->add('dateDep', DateType::class, [
                'label' => 'Date de depart',
                'widget' => 'single_text',
            ])
            ->add('dateRet', DateType::class, [
                'label' => 'Date de retour',
                'widget' => 'single_text',
            ])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contrat = $form->getData();
            $entityManager->persist($contrat);
            $entityManager->flush();
            return $this->redirectToRoute('agent_contrat');
        }

        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/agent/contrats/{id}/modifier", name="agent_contrat_modif")
     */
    public function modifierContrat(string $id, Request $request, ContratRepository $repository, ClientRepository $clientRepository, VoitureRepository $voitureRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $contrat = $repository->find($id);

        $clients = $clientRepository->findAll();
        $choixClient = array();

        foreach ($clients as $client) {
            $choixClient[$client->getNom()] = $client;
        }

        $voitures = $voitureRepository->findAll();
        $choixVoiture = array();

        foreach ($voitures as $voiture) {
            $choixVoiture[$voiture->getMarque()] = $voiture;
        }

        $form = $this->createFormBuilder($contrat)
            ->add('client', ChoiceType::class, [
                'choices' => $choixClient
            ])
            ->add('voiture', ChoiceType::class, [
                'choices' => $choixVoiture
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'type 1' => 1,
                    'type 2' => 2,
                    'type 3' => 3,
                ]
            ])
            ->add('dateDep', DateType::class, [
                'label' => 'Date de depart',
                'widget' => 'single_text',
            ])
            ->add('dateRet', DateType::class, [
                'label' => 'Date de retour',
                'widget' => 'single_text',
            ])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('agent_contrat');
        }
        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/agent/contrats/{id}/supprimer", name="agent_contrat_supp")
     */
    public function supprimerContrat(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Contrat::class);
        $contrat = $repository->find($id);
        $entityManager->remove($contrat);
        $entityManager->flush();
        return $this->redirectToRoute("agent_contrat");
    }
}
