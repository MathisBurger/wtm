<?php

namespace App\Service;

use App\Entity\SpecialDayRequest;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Twig\Environment;

/**
 * The mail service for the system
 */
class MailService
{

    public function __construct(
        private readonly Environment $twig,
        private readonly MailerInterface $mailer
    )
    {}

    /**
     * Sends the request handle email
     *
     * @param SpecialDayRequest $request The request
     * @param string $action The action
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendRequestHandleMail(SpecialDayRequest $request, string $action): void
    {
        $html = $this->twig->render('generator/specialDayRequestMail.html.twig', [
            'actionStatus' => $action === 'accept' ? 'angenommen' : 'abgelehnt',
            'reason' => $request->getReason(),
            'startDate' => $request->getStartDateString(),
            'endDate' => $request->getEndDateString(),
            'notes' => $request->getNotes()
        ]);
        if ($request->getRespondEmail() !== null) {
            $this->mailer->send(
                new RawMessage($html),
                new Envelope(
                    new Address('wtm@ad.dreessen.biz'),
                    array(new Address($request->getRespondEmail()))
                )
            );
        }
    }

}