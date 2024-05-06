<?php

namespace App\Controller;

use App\Service\CheckInOutService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for handling check in and out actions
 */
class CheckInOutController extends AbstractController
{

    public function __construct(
        private readonly CheckInOutService $checkInOutService
    ){}

    /**
     * Performs check in action
     */
    #[Route('/api/v1/check-in/{username}', name: 'api_v1_check_in')]
    public function checkIn(string $username): Response
    {
        $resp = $this->checkInOutService->checkIn(strtolower($username));
        switch ($resp) {
            case CheckInOutService::SUCCESS:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'CheckIn erfolgreich',
                    'messageStatus' => 'is-success',
                    'detailed' => 'Sie wurden erfolgreich angemeldet. Bitte melden Sie sich nach der Arbeit wieder ab, damit die Daten optimal verarbeitet werden können'
                ]);
            case CheckInOutService::ALREADY_CHECKED_IN:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'Sie sind bereits eingeloggt',
                    'messageStatus' => 'is-danger',
                    'detailed' => 'Sie sind bereits angemeldet. Bitte melden sie sich zuerst ab, bevor sie sich erneut anmelden. Sollten sie Probleme haben melden sie sich bitte bei ihrem Administrator'
                ]);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'Der angegebene Nutzer existiert nicht',
                    'messageStatus' => 'is-danger',
                    'detailed' => 'Sie sind nicht als Nutzer im System registriert. Melden sie sich bitte bei ihrem Administrator für weitere Informationen'
                ]);
        }
        return $this->render('checkInOut/message.html.twig', ['message' => 'Anfrage konnte nicht verarbeitet werden', 'messageStatus' => 'is-danger', 'detailed' => '']);
    }

    /**
     * Performs check out action
     */
    #[Route('/api/v1/check-out/{username}', name: 'api_v1_check_out')]
    public function checkOut(string $username): Response
    {
        $resp = $this->checkInOutService->checkOut(strtolower($username));
        switch ($resp) {
            case CheckInOutService::SUCCESS:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'CheckOut erfolgreich',
                    'messageStatus' => 'is-success',
                    'detailed' => 'Sie wurden erfolgreich abgemeldet. Bitte melden Sie sich vor der Arbeit wieder an, damit die Daten optimal verarbeitet werden können'
                ]);
            case CheckInOutService::NOT_CHECKED_IN:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'Sie sind nicht eingeloggt',
                    'messageStatus' => 'is-danger',
                    'detailed' => 'Sie sind noch nicht angemeldet. Bitte melden sie sich zuerst an, bevor sie sich erneut abmelden. Sollten sie Probleme haben melden sie sich bitte bei ihrem Administrator'
                ]);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                return $this->render('checkInOut/message.html.twig', [
                    'message' => 'Der angegebene Nutzer existiert nicht',
                    'messageStatus' => 'is-danger',
                    'detailed' => 'Sie sind nicht als Nutzer im System registriert. Melden sie sich bitte bei ihrem Administrator für weitere Informationen'
                ]);
        }
        return $this->render('checkInOut/message.html.twig', ['message' => 'Anfrage konnte nicht verarbeitet werden', 'messageStatus' => 'is-danger', 'detailed' => '']);
    }


}