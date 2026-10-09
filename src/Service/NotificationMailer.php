<?php

namespace App\Service;

use App\Entity\ContactMessage;
use App\Entity\QuoteRequest;
use App\Entity\SavRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Notifies the admin mailbox whenever a visitor submits the contact,
 * SAV, or quote-request form. A delivery failure never breaks the
 * form submission itself — the message is already saved in the DB
 * and visible in the admin panel either way.
 */
class NotificationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $fromEmail,
        private readonly string $notifyEmail,
        private readonly string $companyName,
    ) {
    }

    public function notifyContactMessage(ContactMessage $message): void
    {
        $this->send(
            subject: 'Nouveau message de contact — '.$message->getSubject(),
            lines: [
                'Nom' => $message->getName(),
                'Email' => $message->getEmail(),
                'Téléphone' => $message->getPhone(),
                'Sujet' => $message->getSubject(),
                'Message' => $message->getMessage(),
            ],
            replyTo: $message->getEmail(),
        );
    }

    public function notifySavRequest(SavRequest $request): void
    {
        $this->send(
            subject: 'Nouvelle demande SAV — '.$request->getSubject(),
            lines: [
                'Nom' => trim($request->getFirstName().' '.$request->getLastName()),
                'Email' => $request->getEmail(),
                'Téléphone' => $request->getPhone(),
                'Produit concerné' => $request->getProduct()?->getName(),
                'Sujet' => $request->getSubject(),
                'Message' => $request->getMessage(),
            ],
            replyTo: $request->getEmail(),
        );
    }

    public function notifyQuoteRequest(QuoteRequest $request): void
    {
        $this->send(
            subject: 'Nouvelle demande de devis'.($request->getProduct() ? ' — '.$request->getProduct()->getName() : ''),
            lines: [
                'Nom' => $request->getName(),
                'Email' => $request->getEmail(),
                'Téléphone' => $request->getPhone(),
                'Produit concerné' => $request->getProduct()?->getName(),
                'Message' => $request->getMessage(),
            ],
            replyTo: $request->getEmail(),
        );
    }

    /**
     * @param array<string, ?string> $lines
     */
    private function send(string $subject, array $lines, ?string $replyTo): void
    {
        $body = '';
        foreach ($lines as $label => $value) {
            if ($value) {
                $body .= $label." : {$value}\n\n";
            }
        }

        $email = (new Email())
            ->from($this->fromEmail)
            ->to($this->notifyEmail)
            ->subject("[{$this->companyName}] {$subject}")
            ->text($body);

        if ($replyTo) {
            $email->replyTo($replyTo);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send notification email: '.$e->getMessage());
        }
    }
}
