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
                return $this->render('general/message.html.twig', [
                    'message' => 'CheckIn erfolgreich',
                    'messageStatus' => 'alert-success',
                ]);
            case CheckInOutService::ALREADY_CHECKED_IN:
                return $this->render('general/message.html.twig', [
                    'message' => 'Sie sind bereits eingeloggt',
                    'messageStatus' => 'alert-danger'
                ]);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                return $this->render('general/message.html.twig', [
                    'message' => 'Der angegebene Nutzer existiert nicht',
                    'messageStatus' => 'alert-danger'
                ]);
        }
        return $this->render('general/message.html.twig', ['message' => 'Anfrage konnte nicht verarbeitet werden', 'messageStatus' => 'alert-danger']);
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
                return $this->render('general/message.html.twig', [
                    'message' => 'CheckOut erfolgreich',
                    'messageStatus' => 'alert-success'
                ]);
            case CheckInOutService::NOT_CHECKED_IN:
                return $this->render('general/message.html.twig', [
                    'message' => 'Sie sind nicht eingeloggt',
                    'messageStatus' => 'alert-danger'
                ]);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                return $this->render('general/message.html.twig', [
                    'message' => 'Der angegebene Nutzer existiert nicht',
                    'messageStatus' => 'alert-danger',
                ]);
        }
        return $this->render('general/message.html.twig', ['message' => 'Anfrage konnte nicht verarbeitet werden', 'messageStatus' => 'alert-danger']);
    }


}