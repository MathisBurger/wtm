<?php

namespace App\Service;

use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\Generator\ReportPdf;
use App\Repository\WorktimePeriodRepository;
use App\Repository\WorktimeSpecialDayRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;
use Twig\Environment;

class GeneratorService
{

    public function __construct(
        private readonly WorktimePeriodRepository $periodRepository,
        private readonly WorktimeSpecialDayRepository $specialDayRepository,
        private readonly Environment $environment,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Generates a report
     *
     * @param string $period
     */
    public function generateReport(string $period): void
    {
        $entries = $this->periodRepository->findForPeriod($period);
        $employees = [];
        /** @var WorktimePeriod $entry */
        foreach ($entries as $entry) {
            $employees[$entry->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $entry->getStartTime(),
                'fullName' => $entry->getEmployee()->getFirstName() . " " . $entry->getEmployee()->getLastName() . ' (' . $entry->getEmployee()->getUsername() .')',
                'date' => $entry->getStartTime()->format('d.m.Y'),
                'startTime' => $entry->getStartTime()->format('H:i'),
                'endTime' => $entry->getEndTime() !== null ? $entry->getEndTime()->format('H:i') : '-',
                'notes' => ''
            ];
        }
        $specialDays = $this->specialDayRepository->findForPeriod($period);
        /** @var WorktimeSpecialDay $specialDay */
        foreach ($specialDays as $specialDay) {
            $notes = $this->translator->trans('specialDay.' . $specialDay->getReason());
            if ($specialDay->getNotes() !== null) {
                $notes = $notes .': ' . $specialDay->getNotes();
            }
            $employees[$specialDay->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $specialDay->getDate(),
                'fullName' => $specialDay->getEmployee()->getFirstName() . " " . $specialDay->getEmployee()->getLastName() . ' (' . $specialDay->getEmployee()->getUsername() .')',
                'date' => $specialDay->getDate()->format('d.m.Y'),
                'startTime' => '-',
                'endTime' => '-',
                'notes' => $notes
            ];
        }

        /**
         * @var string $key
         * @var array $value
         */
        foreach ($employees as $_ => $value) {
            usort(
                $value,
                fn (array $a, array $b) => $a['dateUnformatted']->getTimeStamp() <=> $b['dateUnformatted']->getTimeStamp()
            );
        }
        $html = $this->environment->render('generator/generator.html.twig', [
            'employees' => array_keys($employees),
            'periods' => $employees
        ]);
        $pdf = new ReportPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->handleCreation($html, $period);

        $filesystem = new Filesystem();
        //$loc = $filesystem->tempnam('/tmp', 'generated_', '.pdf');

        $pdf->Output('Monatsbericht.pdf');
    }
}