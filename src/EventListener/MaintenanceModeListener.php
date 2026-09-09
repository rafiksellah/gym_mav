<?php

namespace App\EventListener;

use App\Service\MaintenanceModeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceModeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MaintenanceModeService $maintenanceMode,
        private readonly Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->maintenanceMode->isEnabled()) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();
        if (str_starts_with($path, '/admin')) {
            return;
        }

        $response = new Response(
            $this->twig->render('front/maintenance.html.twig'),
            Response::HTTP_SERVICE_UNAVAILABLE
        );
        $response->headers->set('Retry-After', '3600');
        $event->setResponse($response);
    }
}
