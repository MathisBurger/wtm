<?php

namespace App\Controller;

use App\Form\RequestSpecialDayType;
use App\Form\WorktimeSpecialDaySkeletonType;
use App\Repository\EmployeeRepository;
use App\Service\SpecialDayRequestService;
use App\Voter\LdapAdminVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles special day request requests
 */
class SpecialDayRequestController extends AbstractController
{

    public function __construct(
        private readonly SpecialDayRequestService $requestService,
        private readonly Security $security,
        private readonly EmployeeRepository $employeeRepository
    ) {}

    /**
     * Lists all special day requests
     */
    #[Route('/specialDayRequests', name: 'special_day_requests_list')]
    public function listSpecialDayRequests(): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        return $this->render('specialDayRequest/list.html.twig', [
            'requests' => $this->requestService->getRequests()
        ]);
    }

    /**
     * Displays the details of a special day request
     */
    #[Route('/specialDayRequests/details/{id}', name: 'special_day_requests_details')]
    public function specialDayRequestDetails(int $id): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        return $this->render('specialDayRequest/details.html.twig', [
            'request' => $this->requestService->getRequest($id)
        ]);
    }


    /**
     * Lists all special day requests
     */
    #[Route('/specialDayRequests/personal', name: 'special_day_requests_list_personal')]
    public function getPersonalRequestsList(): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::PERSONAL_STATS_ACCESS);
        return $this->render('specialDayRequest/list.html.twig', [
            'requests' => $this->requestService->getPersonalRequests()
        ]);
    }

    /**
     * Downloads the document of a request
     */
    #[Route('/specialDayRequests/downloadFile/{id}', name: 'special_day_requests_downloadFile')]
    public function downloadFile(int $id): Response
    {
        return new BinaryFileResponse($this->requestService->downloadFile($id));
    }

    /**
     * Handles the request to create a special day request
     */
    #[Route('/employee/createSpecialDayRequest', name: 'worktime_specialday_request_create')]
    public function createSpecialDayRequest(Request $request): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::PERSONAL_STATS_ACCESS);
        $form = $this->createForm(RequestSpecialDayType::class, array());
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('specialDayRequest/create.html.twig', [
                'form' => $form,
                'error' => null,
            ]);
        }
        try {
            $this->requestService->addSpecialDayRequest($form);
        } catch (Exception $e) {
            return $this->render('specialDayRequest/create.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
            ]);
        }
        $user = $this->employeeRepository->findOneBy(['username' => $this->security->getUser()->getUserIdentifier()]);
        return $this->redirectToRoute('employee_details', ['id' => $user->getId()]);
    }

}