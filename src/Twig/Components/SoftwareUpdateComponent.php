<?php

namespace App\Twig\Components;

use App\Updater\Updater;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Twig component to handle software updates
 */
#[AsTwigComponent]
class SoftwareUpdateComponent
{

    public function __construct(
        private readonly Updater $updater,
    ) {}

    /**
     * Checks for new updates
     *
     * @return bool If there is a new update
     */
    public function getUpdate(): bool
    {
        return $this->updater->getNewUpdateAvailable();
    }

}