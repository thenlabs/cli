<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ListInstalledPackagesCommand extends ThenCommand
{
    protected static $defaultName = 'list:installed-packages';

    protected function configure()
    {
        $this
            ->setDescription('Lists all the installed packages that type is "then-package".')
            ->addArgument('directory', InputArgument::OPTIONAL, '', getcwd())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getInstalledPackages($input, $output) as $package) {
            $output->writeln($package);
        }

        return 0;
    }
}
