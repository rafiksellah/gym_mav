<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/services')]
class ServiceController extends AbstractController
{
    private const IMAGE_SUBDIR = 'services';

    #[Route('', name: 'admin_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('admin/service/index.html.twig', [
            'services' => $serviceRepository->findBy([], ['position' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $service->setImage($fileUploader->upload($imageFile, self::IMAGE_SUBDIR));
            }

            $em->persist($service);
            $em->flush();

            $this->addFlash('success', 'Service créé avec succès.');

            return $this->redirectToRoute('admin_service_index');
        }

        return $this->render('admin/service/form.html.twig', [
            'form' => $form,
            'service' => $service,
            'isNew' => true,
        ]);
    }

    #[Route('/{id}/edition', name: 'admin_service_edit', methods: ['GET', 'POST'])]
    public function edit(Service $service, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fileUploader->remove($service->getImage(), self::IMAGE_SUBDIR);
                $service->setImage($fileUploader->upload($imageFile, self::IMAGE_SUBDIR));
            }

            $em->flush();

            $this->addFlash('success', 'Service modifié avec succès.');

            return $this->redirectToRoute('admin_service_index');
        }

        return $this->render('admin/service/form.html.twig', [
            'form' => $form,
            'service' => $service,
            'isNew' => false,
        ]);
    }

    #[Route('/{id}/suppression', name: 'admin_service_delete', methods: ['POST'])]
    public function delete(Service $service, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-service-'.$service->getId(), $request->request->get('_token'))) {
            $fileUploader->remove($service->getImage(), self::IMAGE_SUBDIR);
            $em->remove($service);
            $em->flush();

            $this->addFlash('success', 'Service supprimé.');
        }

        return $this->redirectToRoute('admin_service_index');
    }

    #[Route('/{id}/toggle-actif', name: 'admin_service_toggle_active', methods: ['POST'])]
    public function toggleActive(Service $service, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle-service-'.$service->getId(), $request->request->get('_token'))) {
            $service->setIsActive(!$service->isActive());
            $em->flush();
        }

        return $this->redirectToRoute('admin_service_index');
    }
}
