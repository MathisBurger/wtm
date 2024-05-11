<?php

namespace App\Controller;

use App\Service\CheckInOutService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for handling check in and out actions
 */
class CheckInOutController extends AbstractController
{

    public function __construct(
        private readonly CheckInOutService $checkInOutService,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Performs check in action
     */
    #[Route('/api/v1/check-in/{username}', name: 'api_v1_check_in')]
    public function checkIn(
        string $username,
        #[MapQueryParameter] ?string $format,
        #[MapQueryParameter] ?string $device
    ): Response
    {
        $resp = $this->checkInOutService->checkIn(strtolower($username), $device);
        switch ($resp) {
            case CheckInOutService::SUCCESS:
                $args = [
                    'message' => $this->translator->trans('messages.checkIn.successful'),
                    'messageStatus' => 'alert-success',
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::ALREADY_CHECKED_IN:
                $args = [
                    'message' => $this->translator->trans('messages.checkIn.alreadyLoggedIn'),
                    'messageStatus' => 'alert-danger'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                $args = [
                    'message' => $this->translator->trans('messages.userDoesNotExist'),
                    'messageStatus' => 'alert-danger'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::EARLY_LOGIN:
                $args = [
                    'message' => $this->translator->trans('messages.checkIn.earlyLogin'),
                    'messageStatus' => 'alert-danger'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
        }
        $args = ['message' => $this->translator->trans('messages.cannotProcess'), 'messageStatus' => 'alert-danger'];
        return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
    }

    /**
     * Performs check out action
     */
    #[Route('/api/v1/check-out/{username}', name: 'api_v1_check_out')]
    public function checkOut(
        string $username,
        #[MapQueryParameter] ?string $format,
        #[MapQueryParameter] ?string $device
    ): Response
    {
        $resp = $this->checkInOutService->checkOut(strtolower($username), $device);
        switch ($resp) {
            case CheckInOutService::SUCCESS:
                $args = [
                    'message' => $this->translator->trans('messages.checkOut.successful'),
                    'messageStatus' => 'alert-success'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::NOT_CHECKED_IN:
                $args = [
                    'message' => $this->translator->trans('messages.checkOut.notLoggedIn'),
                    'messageStatus' => 'alert-danger'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::USER_DOES_NOT_EXIST:
                $args = [
                    'message' => $this->translator->trans('messages.userDoesNotExist'),
                    'messageStatus' => 'alert-danger',
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
            case CheckInOutService::EARLY_LOGOUT:
                $args = [
                    'message' => $this->translator->trans('messages.checkOut.earlyLogout'),
                    'messageStatus' => 'alert-danger'
                ];
                return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
        }
        $args = ['message' => $this->translator->trans('messages.cannotProcess'), 'messageStatus' => 'alert-danger'];
        return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
    }

    /**
     * Gets the required action
     */
    #[Route('/api/v1/required-action/{username}', name: 'api_v1_required_action')]
    public function getCurrentAction(string $username): Response
    {
        return new Response($this->checkInOutService->getRequiredAction($username));
    }

    /**
     * Provides rdp not allowed message
     */
    #[Route('/api/v1/rdpNotAllowed', name: 'api_v1_rdp_not_allowed')]
    public function rdpNotAllowed(
        #[MapQueryParameter] ?string $format
    ): Response
    {
        $args = [
            'message' => $this->translator->trans('messages.rdpNotAllowed'),
            'messageStatus' => 'alert-danger'
        ];
        return $format === "json" ? $this->json($args) : $this->render('general/message.html.twig', $args);
    }
}