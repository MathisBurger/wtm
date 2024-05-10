<?php

namespace App\Twig\Components;

use App\Entity\ConfiguredWorktime;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Twig component to display work time plan
 */
#[AsTwigComponent]
class WorkTimePlan
{

    /**
     * @var Collection All worktimes
     */
    public Collection $worktimes;

    /**
     * Gets all worktimes for day
     *
     * @param string $day The day that should be filtered for
     * @return array The worktimes for that day
     */
    public function getWorktimesForDay(string $day): array
    {
        return $this->worktimes->filter(
            fn (ConfiguredWorktime $configuredWorktime) => $configuredWorktime->getDayName() === $day
        )->toArray();
    }

    /**
     * Gets a worktime string
     *
     * @param ConfiguredWorktime $worktime The worktime
     * @return string The worktime string
     */
    public function getWorktimeString(ConfiguredWorktime $worktime): string
    {
        return $worktime->getRegularStartTime()->format("H:i") . " - " . $worktime->getRegularEndTime()->format("H:i");
    }

    /**
     * Checks if there are worktimes on a day
     *
     * @param string $day The day that should be checked for
     * @return bool If exists
     */
    public function dayExists(string $day): bool
    {
        return count($this->getWorktimesForDay($day)) > 0;
    }
}