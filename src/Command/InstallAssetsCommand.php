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
        $installedPackages = $this->getInstalledPackages($input, $output);
        $thenJson = $this->getThenJson($input, $output);

        if (! isset($thenJson->targetAssetsDir)) {
            return 0;
        }

        $directory = $input->getArgument('directory');
        $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir;

        $filesystem = new Filesystem;

        foreach ($installedPackages as $package) {
            $packageDir = "{$directory}/vendor/{$package}";
            $thenPackage = json_decode(file_get_contents($packageDir.'/then-package.json'));

            if (! $filesystem->exists($targetAssetsDir)) {
                $filesystem->mkdir($targetAssetsDir);
            }

            foreach ($thenPackage->assets as $assetsDir) {
                $filesystem->mirror($packageDir.'/'.$assetsDir, $targetAssetsDir);
            }
        }

        return 0;
    }
}
