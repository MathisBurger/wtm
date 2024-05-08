<?php

namespace App\Controller;

use App\Updater\Updater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for software updates
 */
class SoftwareUpdateController extends AbstractController
{

    public function __construct(
        private readonly Updater $updater
    ) {}

    /**
     * Renders software update page
     */
    #[Route('/software/update', name: 'software_update_view')]
    public function updatePage(): Response
    {
        if (!$this->updater->getNewUpdateAvailable()) {
            return $this->render('general/message.html.twig', [
                'message' => 'Software is already up to date',
                'messageStatus' => 'alert-success'
            ]);
        }
        $updateInfo = $this->updater->getLatestRelease();
        return $this->render('software/update.html.twig', [
            'tagName' => $updateInfo['tag_name'],
            'content' => $updateInfo['body']
        ]);
    }

    /**
     * Performs the actual software update
     *
     * @return Response
     */
    #[Route('/software/update/perform', name: 'software_update_perform')]
    public function performUpdate(): Response
    {
        return $this->render('software/perform.html.twig', []);
    }
}