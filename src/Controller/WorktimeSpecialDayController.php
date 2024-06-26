<?php

namespace App\Controller;

use App\Form\WorktimeSpecialDaySkeletonType;
use App\Service\WorktimeSpecialDayService;
use App\Voter\LdapAdminVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles special day requests
 */
class WorktimeSpecialDayController extends AbstractController
{

    public function __construct(
        private readonly WorktimeSpecialDayService  $worktimeSpecialDayService
    ){}

    /**
     * Handles the request to create a special day
     */
    #[Route('/employee/createSpecialDay/{id}', name: 'worktime_specialday_create')]
    public function createSpecialDay(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        $form = $this->createForm(WorktimeSpecialDaySkeletonType::class, array());
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('specialDay/create.html.twig', [
                'form' => $form,
                'error' => null,
            ]);
        }
        try {
            $this->worktimeSpecialDayService->createSpecialDays($id, $form);
        } catch (Exception $e) {
            return $this->render('specialDay/create.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
            ]);
        }
        return $this->redirectToRoute('employee_details', ['id' => $id]);
    }

    /**
     * Deletes a special day
     */
    #[Route('/employee/deleteSpecialDay/{id}', name: 'worktime_specialday_delete')]
    public function deleteSpecialDay(int $id): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        try {
            $employeeId = $this->worktimeSpecialDayService->deleteSpecialDays($id);
            return $this->redirectToRoute('employee_details', ['id' => $employeeId]);
        } catch (Exception $e) {}
        return new Response();
    }

}