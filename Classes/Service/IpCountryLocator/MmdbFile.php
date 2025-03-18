<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use GuzzleHttp\Client;
use MaxMind\Db\Reader;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MmdbFile implements IpCountryLocatorInterface, SingletonInterface
{
    private Reader $reader;

    public function __construct(
        private readonly Client $client,
        private readonly string $maxmindLicenseKey,
    ) {
    }

    public function getIpCountry(): ?string
    {
        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (!$ip) {
            return null;
        }

        $country = $this->getReader()->get($ip)['country']['iso_code'] ?? '';
        return strtolower((string)$country) ?: null;
    }

    public function getDebugInfo(): string
    {
        $databaseFile = $this->getDatabaseFileName();
        $mmdbFileExists = file_exists($databaseFile);
        return implode(PHP_EOL, [
            'mmdb file time: ' . ($mmdbFileExists ? date('Y-m-d H:i:s', (int)filemtime($databaseFile)) : 'not found'),
            'mmdb size: ' . ($mmdbFileExists ? $this->humanFilesize((int)filesize($databaseFile)) : 'not found'),
            'ip used: ' . GeneralUtility::getIndpEnv('REMOTE_ADDR'),
        ]);
    }


    public function getReader(): Reader
    {
        if ($this->reader ?? null) {
            return $this->reader;
        }

        $databaseFile = $this->getDatabaseFileName();

        //filemtime should get the original creation time of the database
        if (!file_exists($databaseFile) || (filemtime($databaseFile) < strtotime('-5 weeks'))) {
            // only update after 5 weeks via WebRequests (you should add the scheduler task)
            $this->downloadNewestFile();
        }

        return $this->reader = new Reader($databaseFile);
    }

    public function downloadNewestFile(?OutputInterface $output = null): bool
    {
        if (!$this->maxmindLicenseKey) {
            $output?->writeln('<error> no maxmindLicenseKey defined. </error>');
            return false;
        }

        $databaseFile = $this->getDatabaseFileName();
        $tarFile = $databaseFile . '.tar.gz';

        //filectime should get the time of last the download action
        if (file_exists($databaseFile) && filectime($databaseFile) > strtotime('-1 day')) {
            $output?->writeln('<comment>did not update ' . $databaseFile . ' as the file is not older than 1 Day</comment>');
            return true;
        }

        $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=' . $this->maxmindLicenseKey . '&suffix=tar.gz';
        $output?->writeln('start download of ' . $tarFile . ' ...');
        file_put_contents($tarFile, $this->client->get($url)->getBody()->getContents());
        $output?->writeln('Downloaded mmdb file into ' . $tarFile . ' with size: ' . $this->humanFilesize((int)filesize(($tarFile))));

        $output?->writeln('now extracting file ...');
        $command = "tar --strip-components=1 -C " . dirname($databaseFile) . " -xzf " . $tarFile . " --wildcards --no-anchored '*.mmdb'";
        $output?->writeln('<info>' . $command . '</info>');
        shell_exec($command);

        $glob = glob(dirname($databaseFile) . '/*.mmdb');
        if (!$glob) {
            throw new RuntimeException('no mmdb file found in tar of the maxmind API', 3770624500);
        }

        foreach ($glob as $mmdbFile) {
            if ($mmdbFile === $databaseFile) {
                continue;
            }

            rename($mmdbFile, $databaseFile);
        }

        $output?->writeln('extracted file into ' . $databaseFile . ' and with size: ' . $this->humanFilesize((int)filesize($databaseFile)));

        unlink($tarFile);
        $output?->writeln('<info>Database Updated</info>');
        return true;
    }

    private function getDatabaseFileName(): string
    {
        $extractionDestination = Environment::getVarPath() . '/geo_redirect/';
        GeneralUtility::mkdir_deep($extractionDestination);

        return $extractionDestination . 'database.mmdb';
    }


    private function humanFilesize(int $bytes, int $decimals = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        return sprintf(sprintf('%%.%df', $decimals), $bytes / (1024 ** $factor)) . ($size[$factor] ?? '');
    }
}
