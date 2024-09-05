<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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

    public function sendFirstLoginEmail(string $recipientEmail, string $pseudo, string $firstLoginUrl): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@example.com')
            ->to($recipientEmail)
            ->subject('Finalisez votre inscription : Sortir.com')
            ->htmlTemplate('reset_password/first_connexion.html.twig')
            ->context([
                'pseudo' => $pseudo,
                'firstLoginUrl' => $firstLoginUrl,
            ]);

        $this->mailer->send($email);
    }

    // Envoi d'un email pour la réinitialisation de mot de passe
    public function sendPasswordResetEmail(string $recipientEmail, string $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@sortir.com')
            ->to($recipientEmail)
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($email);
    }



}

