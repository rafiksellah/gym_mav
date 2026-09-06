<?php

namespace App\Controller\Front;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'front_service')]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('front/service/index.html.twig', [
            'services' => $serviceRepository->findActiveOrdered(),
        ]);
    }
}
