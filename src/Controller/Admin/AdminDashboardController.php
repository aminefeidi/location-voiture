<?php

namespace App\Controller\Admin;

use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\VoitureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(
        VoitureRepository $voitureRepository,
        FactureRepository $factureRepository,
        ContratRepository $contratRepository
    ): Response
    {
        $nbrVoituresDisp = $voitureRepository->createQueryBuilder('v')
            ->where('v.disponibilite = 1')
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
            ->where('c.dateRet < :date AND v.disponibilite = 0')
            ->setParameters([
                'date' => date('Y-m-d')
            ])
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        return $this->render('admin/index.html.twig', [
            'voituresDisp' => $nbrVoituresDisp,
            'voituresNonRendues' => $nbrVoituresNonRendues,
            'facturesImp' => $nbrFacturesImp,
        ]);
    }


    
}
