<?php

namespace App\Controller;

use App\Updater\Updater;
use App\Voter\LdapAdminVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for software updates
 */
class SoftwareUpdateController extends AbstractController
{

    public function __construct(
        private readonly Updater $updater,
        private readonly KernelInterface $kernel,
        private readonly TranslatorInterface $translator
    ) {}

    /**
     * Renders software update page
     */
    #[Route('/software/update', name: 'software_update_view')]
    public function updatePage(): Response
    {
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        if (!$this->updater->getNewUpdateAvailable()) {
            return $this->render('general/message.html.twig', [
                'message' => $this->translator->trans('messages.softwareAlreadyUpToDate'),
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
        $this->denyAccessUnlessGranted(LdapAdminVoter::ADMIN_ACCESS);
        if (!$this->updater->getNewUpdateAvailable()) {
            return $this->render('general/message.html.twig', [
                'message' => $this->translator->trans('messages.softwareAlreadyUpToDate'),
                'messageStatus' => 'alert-success'
            ]);
        }
        $updateInfo = $this->updater->getLatestRelease();
        $version = $updateInfo['tag_name'];
        if (str_starts_with($version, 'v')) {
            $version = ltrim($version, 'v');
        }
        $process = Process::fromShellCommandline('nohup ./updateSoftware.sh ' . $version . '> foo.out 2> foo.err < /dev/null &');
        $process->setWorkingDirectory($this->kernel->getProjectDir());
        $process->run();
        return $this->render('software/perform.html.twig', []);
    }
}