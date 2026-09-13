<?php

namespace App\Controller\Admin;

use App\Form\CompanySettingsType;
use App\Repository\CompanySettingsRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/facturation/parametres')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class CompanySettingsController extends AbstractController
{
    private const LOGO_SUBDIR = 'company';

    #[Route('', name: 'admin_company_settings', methods: ['GET', 'POST'])]
    public function edit(Request $request, CompanySettingsRepository $repository, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $settings = $repository->getOrCreate();

        $form = $this->createForm(CompanySettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logoFile')->getData();
            if ($logoFile) {
                $fileUploader->remove($settings->getLogoFilename(), self::LOGO_SUBDIR);
                $settings->setLogoFilename($fileUploader->upload($logoFile, self::LOGO_SUBDIR));
            }

            $em->flush();

            $this->addFlash('success', 'Paramètres de facturation enregistrés.');

            return $this->redirectToRoute('admin_company_settings');
        }

        return $this->render('admin/company_settings/edit.html.twig', [
            'form' => $form,
            'settings' => $settings,
        ]);
    }
}
