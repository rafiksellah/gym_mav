<?php

namespace App\Controller\Front;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'front_home')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        return $this->render('front/home/index.html.twig', [
            'categories' => $categoryRepository->findActiveOrdered(),
            'featuredProducts' => $productRepository->findFeatured(6),
            'services' => $serviceRepository->findActiveOrdered(),
        ]);
    }
}
