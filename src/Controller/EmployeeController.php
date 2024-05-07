<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Exception\EmployeeException;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller handling employee actions
 */
class EmployeeController extends AbstractController
{

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EmployeeService $employeeService
    ) {}

    #[Route('/employees/details/{id}', name: 'employee_details')]
    public function viewDetails(
        int $id,
        #[MapQueryParameter] ?string $tab
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $employee = $this->employeeRepository->find($id);
        if (!$employee) {
            return $this->render('general/message.html.twig', [
                'message' => 'Unbekannter Nutzer',
                'messageStatus' => 'alert-danger'
            ]);
        }
        return $this->render('employee/details.html.twig', [
            'employee' => $employee,
            'tab' => $tab,
            'periods' => $employee->getPeriods()->filter(
                fn (WorktimePeriod $p) => $p->getStartTime()->format("m") === (new \DateTime())->format("m")
            )
        ]);
    }

    /**
     * Create employee
     */
    #[Route('/employees/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function createEmployee(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(EmployeeType::class, new Employee(), [
            'action' => $this->generateUrl('employee_create'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => null,
                'title' => 'Mitarbeiter anlegen'
            ]);
        }
        try {
            $result = $this->employeeService->createEmployee($form);
            if ($result === null) {
                return $this->render('employee/createUpdate.html.twig', [
                    'form' => $form,
                    'error' => 'Das Formular wurde nicht richtig ausgefüllt.',
                    'title' => 'Mitarbeiter anlegen'
                ]);
            }
            return $this->redirectToRoute('employee_details', ['id' => $result->getId()]);
        } catch (Exception $e) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
                'title' => 'Mitarbeiter anlegen'
            ]);
        }
    }

    /**
     * Update employee
     */
    #[Route('/employees/update/{id}', name: 'employee_update', methods: ['GET', 'POST'])]
    public function updateEmployee(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $exists = $this->employeeRepository->find($id);
        $form = $this->createForm(EmployeeType::class, new Employee(), ['data' => $exists]);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => null,
                'title' => 'Mitarbeiter bearbeiten'
            ]);
        }
        try {
            $result = $this->employeeService->updateEmployee($form);
            if ($result === null) {
                return $this->render('employee/createUpdate.html.twig', [
                    'form' => $form,
                    'error' => 'Das Formular wurde nicht richtig ausgefüllt.',
                    'title' => 'Mitarbeiter bearbeiten'
                ]);
            }
            return $this->redirectToRoute('employee_details', ['id' => $result->getId()]);
        } catch (Exception $e) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
                'title' => 'Mitarbeiter bearbeiten'
            ]);
        }
    }

    #[Route('/employees', name: 'employee_list')]
    public function list(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $employees = $this->employeeRepository->findAll();
        return $this->render('employee/list.html.twig', [
            'employees' => $employees,
        ]);
    }

}