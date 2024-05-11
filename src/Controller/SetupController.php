<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\FormException;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller to set up this application
 */
class SetupController extends AbstractController
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Sets up the application
     */
    #[Route('/setup-application', name: 'setup_application', methods: ['GET'])]
    public function index(): Response
    {
        if ($this->userRepository->count() > 0) {
            return $this->render('general/message.html.twig', [
                'message' => $this->translator->trans('error.alreadySetUp'),
                'messageStatus' => 'alert-danger'
            ]);
        }
        return $this->render('setup/index.html.twig', [
            'errorMessage' => null,
            'form' => $this->createForm(UserType::class),
        ]);
    }

    /**
     * Sets up the application
     */
    #[Route('/setup-application', name: 'setup_application_post', methods: ['POST'])]
    public function setup(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        try {
            $this->userService->createAdminUser($form);
        } catch (FormException $e) {
            return $this->render('setup/index.html.twig', [
                'errorMessage' => $e->getMessage(),
                'form' => $form->createView(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->render('setup/index.html.twig', [
                'errorMessage' => $this->translator->trans('error.alreadySetUp'),
                'form' => $form->createView(),
            ]);
        }
        return $this->redirect("/");
    }

}