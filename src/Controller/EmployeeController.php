<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\Exception\EmployeeException;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use App\Service\GeneratorService;
use App\Utility\EmployeeUtility;
use App\Utility\PeriodUtility;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller handling employee actions
 */
class EmployeeController extends AbstractController
{

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EmployeeService $employeeService,
        private readonly GeneratorService $generatorService,
        private readonly TranslatorInterface $translator
    ) {}

    /**
     * Renders all details of user
     */
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
                'message' => $this->translator->trans('messages.unknownUser'),
                'messageStatus' => 'alert-danger'
            ]);
        }

        [$periods, $overtime, $firstPeriodStartTime, $holidays, $illnessDays] = EmployeeUtility::getEmployeeData($employee);

        return $this->render('employee/details.html.twig', [
            'employee' => $employee,
            'tab' => $tab,
            'overtimeTransfer' => $employee->getOvertime() + $this->generatorService->getOvertime($employee, $firstPeriodStartTime),
            'overtime' => $overtime,
            'overtimeSum' => $employee->getOvertime() + $this->generatorService->getOvertime($employee, $firstPeriodStartTime) + $overtime,
            'periods' => $periods,
            'holidays' => $holidays,
            'illnessDays' => $illnessDays
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
                'title' => $this->translator->trans('title.createEmployee'),
                'isUpdate' => false
            ]);
        }
        try {
            $result = $this->employeeService->createEmployee($form);
            if ($result === null) {
                return $this->render('employee/createUpdate.html.twig', [
                    'form' => $form,
                    'error' => $this->translator->trans('error.invalidForm'),
                    'title' => $this->translator->trans('title.createEmployee'),
                    'isUpdate' => false
                ]);
            }
            return $this->redirectToRoute('employee_details', ['id' => $result->getId()]);
        } catch (Exception $e) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
                'title' => $this->translator->trans('title.createEmployee'),
                'isUpdate' => false
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
                'title' => $this->translator->trans('title.editEmployee'),
                'isUpdate' => true,
                'employee' => $exists
            ]);
        }
        try {
            $result = $this->employeeService->updateEmployee($form);
            if ($result === null) {
                return $this->render('employee/createUpdate.html.twig', [
                    'form' => $form,
                    'error' => $this->translator->trans('error.invalidForm'),
                    'title' => $this->translator->trans('title.editEmployee'),
                    'isUpdate' => true,
                    'employee' => $exists
                ]);
            }
            return $this->redirectToRoute('employee_details', ['id' => $result->getId()]);
        } catch (Exception $e) {
            return $this->render('employee/createUpdate.html.twig', [
                'form' => $form,
                'error' => $e->getMessage(),
                'title' => $this->translator->trans('title.editEmployee'),
                'isUpdate' => true,
                'employee' => $exists
            ]);
        }
    }

    /**
     * Deletes an employee
     */
    #[Route('/employees/delete/{id}', name: 'employee_delete', methods: ['GET'])]
    public function deleteEmployee(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->employeeService->deleteEmployee($id);
        return $this->redirectToRoute('employee_list');
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