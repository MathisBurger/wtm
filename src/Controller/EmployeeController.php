<?php

namespace App\Controller;

use App\Exception\EmployeeException;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function viewDetails(int $id): Response
    {
        return new Response();
    }

    /**
     * Create employee
     */
    #[Route('/employees/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function createEmployee(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(EmployeeType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('employee/create.html.twig', [
                'form' => $form,
                'error' => null,
            ]);
        }
        try {
            $result = $this->employeeService->createEmployee($form);
            if ($result === null) {
                return $this->render('employee/create.html.twig', [
                    'form' => $form,
                    'error' => 'Das Formular wurde nicht richtig ausgefüllt.'
                ]);
            }
            return $this->redirectToRoute('employee_details', ['id' => $result->getId()]);
        } catch (EmployeeException $e) {
            return $this->render('employee/create.html.twig', [
                'form' => $this->createForm(EmployeeType::class),
                'error' => 'Das Formular wurde nicht richtig ausgefüllt.'
            ]);
        } catch (Exception $e) {
            return $this->render('employee/create.html.twig', [
                'form' => $form,
                'error' => $e->getMessage()
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