<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Index controller
 */
class IndexController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ){}

    /**
     * Index route
     */
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(): Response {
        $count = $this->userRepository->count();
        if ($count === 0) {
            return $this->redirect('/setup-application');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('landing/index.html.twig', []);
    }

}