<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Listing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstalledPackagesCommand extends Command
{
    protected static $defaultName = 'list:installed-packages';

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('the composer.lock file is missing.');

        return 0;
    }
}
