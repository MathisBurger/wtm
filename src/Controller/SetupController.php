<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The controller to set up this application
 */
class SetupController extends AbstractController
{

    public function __construct(
        private readonly UserRepository $userRepository,
    ){}

    #[Route('/setup-application', name: 'setup_application')]
    public function index()
    {
        if ($this->userRepository->count() > 0) {
            return $this->render('general/message.html.twig', [
                'message' => 'Die Anwendung wurde bereits eingerichtet',
                'messageStatus' => 'is-danger',
                'detailed' => 'Die Anwendung wurde bereits eingerichtet. Wenn sie die Anwendung erneut installieren wollen melden sie sich bei ihrem Administrator',
            ]);
        }
    }

}