<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendMailService
{
    protected $mailer;
    protected $security;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $from,
        string $name,
        string $to,
        string $subject,
        string $template,
        array $context
    ) {
        $email = new TemplatedEmail();
        $email->from(new Address($from, $name))
            ->to($to)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context)
            ->subject($subject);

        $this->mailer->send($email);
    }
}
