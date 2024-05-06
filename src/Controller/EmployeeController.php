<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller handling employee actions
 */
class EmployeeController extends AbstractController
{

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
    ) {}

    #[Route('/employees', name: 'employee_list')]
    public function list(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $employees = $this->employeeRepository->findAll();
        return $this->render('employee/list.html.twig', [
            'employees' => $employees,
        ]);
    }

}