<?php

namespace App\Updater;

use Github\Client;
use PHPUnit\Util\Exception;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpClient\HttplugClient;

/**
 * Software updater class that handles internal software updates
 */
class Updater
{

    private AdapterInterface $cache;

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Checks if a new update is available
     *
     * @return bool Wheather it is available
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getNewUpdateAvailable(): bool
    {
        if (isset($_ENV['IS_DOCKER']) && $_ENV['IS_DOCKER'] === 'true') {
            return false;
        }
        $latestRelease = $this->cache->getItem("latest_release");
        if (!$latestRelease->isHit()) {
            $raw = $this->callAPI('GET', 'https://api.github.com/repos/MathisBurger/wtm/releases/latest');
            $json = json_decode($raw, true);
            $latestRelease->expiresAfter(10);
            $latestRelease->set($json['tag_name']);
            $this->cache->save($latestRelease);
            return $this->getNewUpdateAvailable();
        }
        return $latestRelease->get() != $this->getSoftwareVersionFromFile();
    }

    /**
     * Gets the current software version from file
     *
     * @return string The version from file
     */
    private function getSoftwareVersionFromFile(): string
    {
        $projectDir = $this->kernel->getProjectDir();
        $filePath = $projectDir . '/SOFTWARE_VERSION';
        $fs = new Filesystem();
        if (!$fs->exists($filePath)) {
            return "";
        }
        return trim(file_get_contents($filePath));
    }

    /**
     * Makes API call to any API out there
     *
     * credits: https://gist.github.com/joashp/808a19837ca4b8a7619d
     *
     * @param string $method The HTTP method
     * @param string $url The url
     * @param mixed $data The data
     * @return bool|string The returned data
     */
    private function callAPI(string $method, string $url, mixed $data = false): bool|string {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}