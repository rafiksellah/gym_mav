<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use App\Repository\ProductImageRepository;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits')]
class ProductController extends AbstractController
{
    private const IMAGE_SUBDIR = 'products';

    #[Route('', name: 'admin_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($this->generateUniqueSlug($product->getName(), $slugger, $productRepository));

            $mainImageFile = $form->get('mainImageFile')->getData();
            if ($mainImageFile) {
                $product->setMainImage($fileUploader->upload($mainImageFile, self::IMAGE_SUBDIR));
            }

            foreach ($form->get('galleryFiles')->getData() ?? [] as $position => $galleryFile) {
                $image = new ProductImage();
                $image->setFilename($fileUploader->upload($galleryFile, self::IMAGE_SUBDIR));
                $image->setPosition($position);
                $product->addImage($image);
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit créé avec succès.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isNew' => true,
        ]);
    }

    #[Route('/{id}/edition', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger, ProductRepository $productRepository): Response
    {
        $originalName = $product->getName();
        $form = $this->createForm(ProductType::class, $product, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($product->getName() !== $originalName) {
                $product->setSlug($this->generateUniqueSlug($product->getName(), $slugger, $productRepository, $product->getId()));
            }

            $mainImageFile = $form->get('mainImageFile')->getData();
            if ($mainImageFile) {
                $fileUploader->remove($product->getMainImage(), self::IMAGE_SUBDIR);
                $product->setMainImage($fileUploader->upload($mainImageFile, self::IMAGE_SUBDIR));
            }

            $existingPositions = array_map(fn (ProductImage $img) => $img->getPosition(), $product->getImages()->toArray());
            $nextPosition = $existingPositions ? max($existingPositions) + 1 : 0;

            foreach ($form->get('galleryFiles')->getData() ?? [] as $galleryFile) {
                $image = new ProductImage();
                $image->setFilename($fileUploader->upload($galleryFile, self::IMAGE_SUBDIR));
                $image->setPosition($nextPosition++);
                $product->addImage($image);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isNew' => false,
        ]);
    }

    #[Route('/{id}/suppression', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-product-'.$product->getId(), $request->request->get('_token'))) {
            $fileUploader->remove($product->getMainImage(), self::IMAGE_SUBDIR);
            foreach ($product->getImages() as $image) {
                $fileUploader->remove($image->getFilename(), self::IMAGE_SUBDIR);
            }

            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/{id}/toggle-actif', name: 'admin_product_toggle_active', methods: ['POST'])]
    public function toggleActive(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle-product-'.$product->getId(), $request->request->get('_token'))) {
            $product->setIsActive(!$product->isActive());
            $em->flush();
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/{id}/toggle-vedette', name: 'admin_product_toggle_featured', methods: ['POST'])]
    public function toggleFeatured(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle-product-'.$product->getId(), $request->request->get('_token'))) {
            $product->setIsFeatured(!$product->isFeatured());
            $em->flush();
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/image/{id}/suppression', name: 'admin_product_image_delete', methods: ['POST'])]
    public function deleteImage(ProductImage $image, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-image-'.$image->getId(), $request->request->get('_token'))) {
            $productId = $image->getProduct()->getId();
            $fileUploader->remove($image->getFilename(), self::IMAGE_SUBDIR);
            $em->remove($image);
            $em->flush();

            return $this->redirectToRoute('admin_product_edit', ['id' => $productId]);
        }

        return $this->redirectToRoute('admin_product_index');
    }

    private function generateUniqueSlug(string $name, SluggerInterface $slugger, ProductRepository $productRepository, ?int $excludeId = null): string
    {
        $baseSlug = (string) $slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 2;

        while (true) {
            $existing = $productRepository->findOneBy(['slug' => $slug]);
            if (!$existing || $existing->getId() === $excludeId) {
                break;
            }
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}
