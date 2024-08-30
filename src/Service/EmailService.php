<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendCancellationEmail(string $recipientEmail, string $eventName, string $reason): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($recipientEmail)
                ->subject('Annulation de l\'événement : ' . $eventName)
                ->text('Nous vous informons que l\'événement "' . $eventName . '" a été annulé pour la raison suivante : ' . $reason);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log or dump the error for debugging purposes
            dump('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
    }

}
