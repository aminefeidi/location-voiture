<?php

namespace App\Controller;

use App\Repository\AgenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
  /**
   * @Route("/")
   */
  public function indexAction(): Response
  {
    return $this->redirectToRoute('app_login');
  }
}
