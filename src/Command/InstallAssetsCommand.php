<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallAssetsCommand extends ThenCommand
{
    protected static $defaultName = 'install:assets';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Install the assets files of the installed then packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $directory = $input->getArgument('directory');
        $installedPackages = $this->getInstalledPackages($input, $output);
        $thenJson = $this->getThenJson($input, $output);
        // $filesystem = new Filesystem;

        // foreach ($installedPackages as $package) {
        //     // [$vendor, $project] = explode('/', $package);

        //     $packageDir = "{$directory}/vendor/{$package}";
        //     $thenPackage = file_get_contents($packageDir.'/then-package.json');

        //     foreach ($thenPackage['assets'] as $assetsDir) {

        //     }

        //     $filesystem->mirror();
        // }

        // $output->writeln('The "then.json" file is missing.');

        return 0;
    }
}
