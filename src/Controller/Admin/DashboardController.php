<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\ContactMessageRepository;
use App\Repository\ProductRepository;
use App\Repository\QuoteRequestRepository;
use App\Repository\SavRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ContactMessageRepository $contactMessageRepository,
        SavRequestRepository $savRequestRepository,
        QuoteRequestRepository $quoteRequestRepository,
    ): Response {
        return $this->render('admin/dashboard/index.html.twig', [
            'productsCount' => $productRepository->count([]),
            'categoriesCount' => $categoryRepository->count([]),
            'contactMessagesCount' => $contactMessageRepository->count([]),
            'contactMessagesUnread' => $contactMessageRepository->countUnread(),
            'savRequestsCount' => $savRequestRepository->count([]),
            'savRequestsUnread' => $savRequestRepository->countUnread(),
            'quoteRequestsCount' => $quoteRequestRepository->count([]),
            'quoteRequestsUnread' => $quoteRequestRepository->countUnread(),
        ]);
    }
}
