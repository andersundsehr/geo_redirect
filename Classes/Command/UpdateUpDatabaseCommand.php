<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Command;

use AUS\GeoRedirect\Service\IpCountryLocator\MmdbFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateUpDatabaseCommand extends Command
{
    public function __construct(private readonly MmdbFile $mmdbFile)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('updates the Ip Database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->mmdbFile->downloadNewestFile($output)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
