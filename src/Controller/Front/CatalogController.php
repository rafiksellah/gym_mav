<?php

namespace App\Controller\Front;

use App\Entity\QuoteRequest;
use App\Form\QuoteRequestType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    private const PRODUCTS_PER_PAGE = 9;

    #[Route('/catalogue', name: 'front_catalog')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categorySlug = $request->query->get('categorie');
        $search = $request->query->get('q');
        $page = max(1, $request->query->getInt('page', 1));

        $qb = $productRepository->createFilteredQueryBuilder($categorySlug, $search);
        $paginator = $productRepository->paginate($qb, $page, self::PRODUCTS_PER_PAGE);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / self::PRODUCTS_PER_PAGE);

        return $this->render('front/product/catalog.html.twig', [
            'products' => $paginator,
            'categories' => $categoryRepository->findActiveOrdered(),
            'currentCategorySlug' => $categorySlug,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/catalogue/{slug}', name: 'front_product_show')]
    public function show(string $slug, Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->findActiveBySlug($slug);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $quoteRequest = new QuoteRequest();
        $quoteRequest->setProduct($product);
        $form = $this->createForm(QuoteRequestType::class, $quoteRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quoteRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de devis a bien été envoyée. Nous vous recontacterons rapidement.');

            return $this->redirectToRoute('front_product_show', ['slug' => $slug]);
        }

        return $this->render('front/product/show.html.twig', [
            'product' => $product,
            'quoteForm' => $form,
        ]);
    }
}
