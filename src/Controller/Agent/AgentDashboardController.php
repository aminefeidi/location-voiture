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

class AgentDashboardController extends AbstractController
{
    /**
     * @Route("/agent", name="agent")
     */
    public function index(
        VoitureRepository $voitureRepository,
        FactureRepository $factureRepository,
        ContratRepository $contratRepository
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $idAgence = $this->getUser()->getAgence()->getId();

        $nbrVoituresDisp = $voitureRepository->createQueryBuilder('v')
            ->where('v.disponibilite = 1 AND v.agence = :agence')
            ->setParameters([
                'agence' => $idAgence
            ])
            ->select('count(v.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $nbrFacturesImp = $factureRepository->createQueryBuilder('f')
            ->where('f.payee = 0')
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $nbrVoituresNonRendues = $contratRepository->createQueryBuilder('c')
            ->leftJoin('c.voiture','v')
            ->where('c.dateRet < :date AND v.disponibilite = 0 AND v.agence = :agence')
            ->setParameters([
                'date' => date('Y-m-d'),
                'agence' => $idAgence
            ])
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('agent/index.html.twig', [
            'voituresDisp' => $nbrVoituresDisp,
            'voituresNonRendues' => $nbrVoituresNonRendues,
            'facturesImp' => $nbrFacturesImp,
        ]);
    }
}
