<?php

namespace App\Controller\Agent;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\Facture;
use App\Repository\ClientRepository;
use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\VoitureRepository;
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

class FactureController extends AbstractController
{
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

        $facture->setDateFac(new DateTime());

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
            ->add('dateFac', DateType::class, [
                'label' => 'Date de facture',
                'widget' => 'single_text',
            ])
            ->add('payee', CheckboxType::class, [
                'label'    => 'Deja payée',
                'required' => false,
            ])
            ->add('sauvegarder', SubmitType::class);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
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
            $form = $event->getForm()->getParent();
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
            $contrat = $form->get('contrat')->getData();
            $diff = $contrat->getDateDep()->diff($contrat->getDateRet());
            $nbrJours = $diff->format('%d');
            $facture->setMontant(100 * $nbrJours);
            $entityManager->persist($facture);
            $entityManager->flush();
            return $this->redirectToRoute('agent_facture');
        }

        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
            'isFacture' => true
        ]);
    }

    /**
     * @Route("/agent/factures/{id}/modifier", name="agent_facture_modif")
     */
    public function modifierFacture(string $id, Request $request, FactureRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $facture = $repository->find($id);

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
            ->add('dateFac', DateType::class, [
                'label' => 'Date de facture',
                'widget' => 'single_text',
            ])
            ->add('payee', CheckboxType::class, [
                'label'    => 'Payement confirmé',
                'required' => false,
            ])
            ->add('sauvegarder', SubmitType::class);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                
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
            $form = $event->getForm()->getParent(); // modifier le parent
            
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
            $contrat = $form->get('contrat')->getData();
            $diff = $contrat->getDateDep()->diff($contrat->getDateRet());
            $nbrJours = $diff->format('%d');
            $facture->setMontant(100 * $nbrJours);
            $entityManager->persist($facture);
            $entityManager->flush();
            return $this->redirectToRoute('agent_facture');
        }

        return $this->render('agent/form.html.twig', [
            'form' => $form->createView(),
            'isFacture' => true
        ]);
    }

    /**
     * @Route("/agent/factures/{id}/supprimer", name="agent_facture_supp")
     */
    public function supprimerFacture(string $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Facture::class);
        $facture = $repository->find($id);
        $entityManager->remove($facture);
        $entityManager->flush();
        return $this->redirectToRoute("agent_facture");
    }
}
