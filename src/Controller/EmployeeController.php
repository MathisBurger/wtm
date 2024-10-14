<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Form\OvertimeDecreaseType;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use App\Service\GeneratorService;
use App\Utility\DateUtility;
use App\Utility\EmployeeUtility;
use App\Utility\PeriodUtility;
use App\Voter\LdapAdminVoter;
use DateTime;
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
        #[MapQueryParameter] ?string $tab,
        #[MapQueryParameter] ?string $timePeriod
    ): Response
    {
        if (!$this->isGranted(LdapAdminVoter::ADMIN_ACCESS) && !$this->isGranted(LdapAdminVoter::PERSONAL_STATS_ACCESS)) {
            throw $this->createAccessDeniedException();
        }
        $employee = $this->employeeRepository->find($id);
        if (!$employee) {
            return $this->render('general/message.html.twig', [
                'message' => $this->translator->trans('messages.unknownUser'),
                'messageStatus' => 'alert-danger'
            ]);
        }
        $currentPeriod = $timePeriod ?? (new DateTime())->format("Y-m");
        $worktime = EmployeeUtility::getWorktimeForPeriods($employee, [$currentPeriod]);
        [$periods, $overtime, $holidays, $illnessDays, $overtimeDecreaseSum] = EmployeeUtility::getEmployeeData($employee, $timePeriod, $tab, $worktime);
        [$workTimePeriods, $holidayPeriods, $illnessPeriods] = EmployeeUtility::getTimePeriodsWithData($employee);
        $adjustedOvertime = $overtime;
        if ($periods->count() > 0 && $periods->last()->getStartTime()->format("Y-m") === (new DateTime())->format("Y-m")) {
            $adjustedOvertime = 0;
        }
        [$year, $month] = PeriodUtility::getYearAndMonthFromPeriod($currentPeriod);
        $newUpdatedAt = DateUtility::getOvertimeLastDayPeriod($year, $month);
        $lastMonthDay = DateUtility::getLastDayOfBeforeMonth($newUpdatedAt);
        return $this->render('employee/details.html.twig', [
            'employee' => $employee,
            'tab' => $tab,
            'overtimeTransfer' => number_format($employee->getOvertimeTransfers()[$lastMonthDay->format('Y-m')] ?? 0, 2),
            'overtime' => number_format($adjustedOvertime, 2),
            'overtimeSum' => number_format($adjustedOvertime + ($employee->getOvertimeTransfers()[$lastMonthDay->format('Y-m')] ?? 0) - $overtimeDecreaseSum, 2),
            'overtimeDecreaseSum' => number_format($overtimeDecreaseSum, 2),
            'periods' => $periods,
            'holidays' => $holidays,
            'illnessDays' => $illnessDays,
            'workTimePeriods' => $workTimePeriods,
            'holidayPeriods' => $holidayPeriods,
            'illnessPeriods' => $illnessPeriods,
            'timePeriod' => $timePeriod
        ]);
    }

    /**
     * Create employee
     */
    #[Route('/employees/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function createEmployee(Request $request): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
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
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
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
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        $this->employeeService->deleteEmployee($id);
        return $this->redirectToRoute('employee_list');
    }

    /**
     * Lists all employees
     */
    #[Route('/employees', name: 'employee_list')]
    public function list(): Response {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        $employees = $this->employeeRepository->findAll();
        return $this->render('employee/list.html.twig', [
            'employees' => $employees,
        ]);
    }

    /**
     * Registers the overtime decrease for an employee
     */
    #[Route('/employee/registerOvertimeDecrease/{id}', name: 'register_overtime_decrease')]
    public function registerOvertimeDecrease(Request $request, int $id): Response
    {
        if (!$this->isGranted(LdapAdminVoter::ADMIN_ACCESS) && !$this->isGranted(LdapAdminVoter::PERSONAL_STATS_ACCESS)) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(OvertimeDecreaseType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('employee/overtimeDecrease.html.twig', [
                'error' => null,
                'form' => $form
            ]);
        }
        try {
            $this->employeeService->registerOvertime($id, $form);
            return $this->redirectToRoute('employee_details', ['id' => $id]);
        } catch (Exception $e) {
            return $this->render('employee/overtimeDecrease.html.twig', [
                'error' => $e->getMessage(),
                'form' => $form
            ]);
        }
    }

}