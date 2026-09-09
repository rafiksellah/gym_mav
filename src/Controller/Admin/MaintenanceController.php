<?php

namespace App\Controller\Admin;

use App\Service\MaintenanceModeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MaintenanceController extends AbstractController
{
    #[Route('/admin/maintenance/toggle', name: 'admin_maintenance_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function toggle(Request $request, MaintenanceModeService $maintenanceMode): Response
    {
        if (!$this->isCsrfTokenValid('toggle-maintenance', $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($maintenanceMode->isEnabled()) {
            $maintenanceMode->disable();
            $this->addFlash('success', 'Le site est de nouveau en ligne pour les visiteurs.');
        } else {
            $maintenanceMode->enable();
            $this->addFlash('success', 'Le site est maintenant en mode maintenance pour les visiteurs.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}
