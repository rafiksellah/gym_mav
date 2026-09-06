<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories')]
class CategoryController extends AbstractController
{
    private const IMAGE_SUBDIR = 'categories';

    #[Route('', name: 'admin_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug($this->generateUniqueSlug($category->getName(), $slugger, $categoryRepository));

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $category->setImage($fileUploader->upload($imageFile, self::IMAGE_SUBDIR));
            }

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie créée avec succès.');

            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'isNew' => true,
        ]);
    }

    #[Route('/{id}/edition', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger, CategoryRepository $categoryRepository): Response
    {
        $originalName = $category->getName();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($category->getName() !== $originalName) {
                $category->setSlug($this->generateUniqueSlug($category->getName(), $slugger, $categoryRepository, $category->getId()));
            }

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fileUploader->remove($category->getImage(), self::IMAGE_SUBDIR);
                $category->setImage($fileUploader->upload($imageFile, self::IMAGE_SUBDIR));
            }

            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès.');

            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'isNew' => false,
        ]);
    }

    #[Route('/{id}/suppression', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-category-'.$category->getId(), $request->request->get('_token'))) {
            if ($category->getProducts()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer une catégorie qui contient des produits.');

                return $this->redirectToRoute('admin_category_index');
            }

            $fileUploader->remove($category->getImage(), self::IMAGE_SUBDIR);
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie supprimée.');
        }

        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/{id}/toggle-actif', name: 'admin_category_toggle_active', methods: ['POST'])]
    public function toggleActive(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle-category-'.$category->getId(), $request->request->get('_token'))) {
            $category->setIsActive(!$category->isActive());
            $em->flush();
        }

        return $this->redirectToRoute('admin_category_index');
    }

    private function generateUniqueSlug(string $name, SluggerInterface $slugger, CategoryRepository $categoryRepository, ?int $excludeId = null): string
    {
        $baseSlug = (string) $slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 2;

        while (true) {
            $existing = $categoryRepository->findOneBy(['slug' => $slug]);
            if (!$existing || $existing->getId() === $excludeId) {
                break;
            }
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}
