<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListPackagesCommand extends ThenCommand
{
    protected static $defaultName = 'list:packages';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Lists all the installed then packages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        foreach ($this->getInstalledPackages($directory, $output) as $package) {
            $output->writeln($package);
        }

        return 0;
    }
}
