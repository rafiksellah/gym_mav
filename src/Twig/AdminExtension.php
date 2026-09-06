<?php

namespace App\Twig;

use App\Repository\ContactMessageRepository;
use App\Repository\QuoteRequestRepository;
use App\Repository\SavRequestRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContactMessageRepository $contactMessageRepository,
        private readonly QuoteRequestRepository $quoteRequestRepository,
        private readonly SavRequestRepository $savRequestRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_unread_messages_count', $this->countUnreadMessages(...)),
        ];
    }

    public function countUnreadMessages(): int
    {
        return $this->contactMessageRepository->countUnread()
            + $this->quoteRequestRepository->countUnread()
            + $this->savRequestRepository->countUnread();
    }
}
