<?php

namespace App\Command;

use App\Entity\WorktimePeriod;
use App\Repository\WorktimePeriodRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command that is executed as a cron job
 * to auto logout users for whom this feature is configured
 */
class AutoLogoutSchedulerCommand extends Command
{

    protected static $defaultName = 'worktime:employee:autoLogout';

    public function __construct(
        private readonly WorktimePeriodRepository $worktimePeriodRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('Europe/Berlin');
        ini_set('date.timezone', 'Europe/Berlin');
        $io = new SymfonyStyle($input, $output);
        $periods = $this->worktimePeriodRepository->findToAutoLogoutByThreshold();
        $io->info(count($periods) . ' employees to auto logout');
        /** @var WorktimePeriod $period */
        foreach ($periods as $period) {
            $date = new DateTime();
            $date->setTime(0,0,0);
            $threshold = $period->getEmployee()->getAutoLogoutThreshold();
            $endDate = (new DateTime())->setTimestamp($date->getTimestamp() + $threshold->getTimestamp()+3600);
            $period->setEndTime($endDate);
            $period->setLogoutDevice("SYSTEM");
            $this->entityManager->persist($period);
            $this->entityManager->flush();
        }
        $io->success("Logged out all sessions in threshold");
        return self::SUCCESS;
    }
}