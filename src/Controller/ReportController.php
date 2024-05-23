<?php

namespace App\Controller;

use App\Repository\WorktimePeriodRepository;
use App\Service\GeneratorService;
use App\Voter\LdapAdminVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller handling reports
 */
class ReportController extends AbstractController
{

    public function __construct(
        private readonly WorktimePeriodRepository $worktimePeriodRepository,
        private readonly GeneratorService $generatorService
    ){}

    /**
     * Renders report options page
     */
    #[Route('/reports', name: 'reports_view')]
    public function getReportOptions(): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        $reports = $this->worktimePeriodRepository->findPeriods();
        return $this->render('reports/list.html.twig', [
            'reports' => array_reverse(array_map(
                fn (\DateTime $dateTime) => $dateTime->format('Y-m'),
                $reports
            ))
        ]);
    }

    /**
     * Generates a report
     * @throws Exception
     */
    #[Route('/api/report/{period}', name: 'report_generate')]
    public function generateReport(string $period)
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        $this->generatorService->generateReport($period);
    }

}