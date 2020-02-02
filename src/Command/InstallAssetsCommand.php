<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class InstallAssetsCommand extends ThenCommand
{
    protected static $defaultName = 'install:assets';

    protected function configure()
    {
        $this
            ->setDescription('Install the assets files of the installed then packages')
            ->addArgument('directory', InputArgument::OPTIONAL, '', getcwd())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getInstalledPackages($input, $output);

        return 0;
    }
}
