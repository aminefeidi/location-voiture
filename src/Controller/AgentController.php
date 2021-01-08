<?php

namespace App\Controller;

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

class AgentController extends AbstractController
{
    /**
     * @Route("/agent", name="agent")
     */
    public function index(): Response
    {
        return $this->render('agent/index.html.twig', [
            'controller_name' => 'AgentController',
        ]);
    }

    /**
     * @Route("/agent/voitures", name="agent_voiture")
     */
    public function voiture(VoitureRepository $repository): Response
    {
        $voitures = $repository->findAll();

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

    /**
     * @Route("/agent/clients", name="agent_client")
     */
    public function client(ClientRepository $repository): Response
    {
        $clients = $repository->findAll();

        return $this->render('agent/client.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * @Route("/agent/clients/ajouter", name="agent_client_ajout")
     */
    public function ajouterClient(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $client = new Client();

        $form = $this->createFormBuilder($client)
            ->add('nom', TextType::class)
            ->add('numPermis', TextType::class, ['label' => 'N° Permis'])
            ->add('ville', TextType::class)
            ->add('tel', TextType::class, ['label' => 'Telephone'])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('agent_client');
        }
        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/agent/clients/{id}/modifier", name="agent_client_modif")
     */
    public function modifierClient(string $id, Request $request, ClientRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $client = $repository->find($id);

        $form = $this->createFormBuilder($client)
            ->add('nom', TextType::class)
            ->add('numPermis', TextType::class, ['label' => 'N° Permis'])
            ->add('ville', TextType::class)
            ->add('tel', TextType::class, ['label' => 'Telephone'])
            ->add('sauvegarder', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('agent_client');
        }
        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/agent/clients/{id}/supprimer", name="agent_client_supp")
     */
    public function supprimerClient(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Client::class);
        $client = $repository->find($id);
        $entityManager->remove($client);
        $entityManager->flush();
        return $this->redirectToRoute("agent_client");
    }

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

        $clients = $clientRepository->findAll();
        $choixClient = array();

        foreach ($clients as $client) {
            $choixClient[$client->getId() . ' - ' . $client->getNom()] = $client;
        }

        $voitures = $voitureRepository->findAll();
        $choixVoiture = array();

        foreach ($voitures as $voiture) {
            if (is_null($voiture->getContrat()) or $voiture->getContrat()->isExpired()) {
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
            ->add('dateDep', DateType::class)
            ->add('dateRet', DateType::class)
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
            ->add('dateDep', DateType::class)
            ->add('dateRet', DateType::class)
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

    /**
     * @Route("/agent/factures", name="agent_facture")
     */
    public function facture(FactureRepository $repository): Response
    {
        $factures = $repository->findAll();

        return $this->render('agent/facture.html.twig', [
            'factures' => $factures,
        ]);
    }

    /**
     * @Route("/agent/factures/ajouter", name="agent_facture_ajout")
     */
    public function ajouterFacture(Request $request, ClientRepository $clientRepository, ContratRepository $contratRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $facture = new Facture();

        $clients = $clientRepository->findAll();
        $choixClient = array();

        foreach ($clients as $client) {
            $choixClient[$client->getId() . ' - ' . $client->getNom()] = $client;
        }

        // $contrats = $contratRepository->findAll();
        // $choixContrat = array();

        // foreach ($contrats as $contrat) {
        //     $choixContrat[$contrat->getId() . ' - ' . $contrat->getClient()->getNom() . ' - ' . $contrat->getVoiture()->getMarque()] = $contrat;
        // }

        $builder = $this->createFormBuilder($facture)
            ->add('client', EntityType::class, [
                'class' => 'App\Entity\Client',
                'placeholder' => '-',
            ])
            ->add('contrat', EntityType::class, [
                'class' => 'App\Entity\Contrat',
                'mapped' => false,
                'placeholder' => '-',
            ])
            ->add('sauvegarder', SubmitType::class);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                // this would be your entity, i.e. SportMeetup
                $client = $event->getData()->getClient();
                $form = $event->getForm();

                $submit = $form->get('sauvegarder');
                $form->remove('sauvegarder');

                $contrats = null === $client ? [] : $client->getContrats();
                $choixContrat = array();

                foreach ($contrats as $contrat) {
                    $dateDepStr = date_format($contrat->getDateDep(), 'd/m/Y');
                    $dateRetStr = date_format($contrat->getDateRet(), 'd/m/Y');
                    $choixContrat[$contrat->getId() . ' - de ' . $dateDepStr . ' à ' . $dateRetStr] = $contrat;
                }

                $form->add('contrat', EntityType::class, [
                    'class' => 'App\Entity\Contrat',
                    'mapped' => false,
                    'placeholder' => '-',
                    'choices' => $choixContrat
                ]);

                $form->add($submit);
            }
        );

        $builder->get('client')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm()->getParent(); // modify the \*\*parent\*\* form
            // during SUBMIT event, ->getData() actually is the resolved object
            $client = $event->getForm()->getData();

            $contrats = null === $client ? [] : $client->getContrats();
            $choixContrat = array();

            foreach ($contrats as $contrat) {
                $dateDepStr = date_format($contrat->getDateDep(), 'd/m/Y');
                $dateRetStr = date_format($contrat->getDateRet(), 'd/m/Y');
                $choixContrat[$contrat->getId() . ' - de ' . $dateDepStr . ' à ' . $dateRetStr] = $contrat;
            }

            $form->add('contrat', EntityType::class, [
                'class' => 'App\Entity\Contrat',
                'mapped' => false,
                'placeholder' => '-',
                'choices' => $choixContrat
            ]);
        });

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facture = $form->getData();
            $entityManager->persist($facture);
            $entityManager->flush();
            return $this->redirectToRoute('agent_facture');
        }

        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
