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

class ClientController extends AbstractController
{
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
}
