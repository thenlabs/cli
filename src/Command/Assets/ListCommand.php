<?php

namespace ThenLabs\Cli\Command\Assets;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected static $defaultName = 'assets:list-packages';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $workingDirectory = $application->getWorkingDirectory();

        $composerLockFile = $workingDirectory.'/composer.lock';

        if (! file_exists($composerLockFile)) {
            $output->writeln('The "composer.lock" file is missing.');
            return Command::FAILURE;
        }

        $composerLockFileContent = json_decode(file_get_contents($composerLockFile), true);

        if (! is_array($composerLockFileContent)) {
            $output->writeln('The "composer.lock" file is corrupt.');
            return Command::FAILURE;
        }

        $installedComposerPackages = $composerLockFileContent['packages'];

        foreach ($installedComposerPackages as $packageData) {
            $packageName = $packageData['name'];
            $packageDir = "{$workingDirectory}/vendor/{$packageName}";
            $thenPackageFile = "{$packageDir}/then-package.json";

            if (file_exists($thenPackageFile)) {
                $thenPackageFileContent = json_decode(file_get_contents($thenPackageFile), true);

                if (! is_array($thenPackageFileContent)) {
                    $output->writeln('The "{$thenPackageFile}" file is corrupt.');
                    return Command::FAILURE;
                }

                if (isset($thenPackageFileContent['assets'])) {
                    $output->writeln($packageName);
                }
            }
        }

        return Command::SUCCESS;
    }
}
