<?php

namespace ThenLabs\Cli\Command\Assets;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;

class ListCommand extends Command
{
    protected static $defaultName = 'assets:list';

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

        $tableRows = [];
        $installedComposerPackages = $composerLockFileContent['packages'];

        foreach ($installedComposerPackages as $packageData) {
            $packageName = $packageData['name'];
            $packageDir = "{$workingDirectory}/vendor/{$packageName}";
            $thenPackageFile = "{$packageDir}/then-package.json";

            if (file_exists($thenPackageFile)) {
                $thenPackageFileContent = json_decode(file_get_contents($thenPackageFile), true);

                if (is_array($thenPackageFileContent) &&
                    isset($thenPackageFileContent['assets']) &&
                    is_array($thenPackageFileContent['assets'])
                ) {
                    $totalOfFiles = 0;
                    $totalSize = 0;

                    foreach ($thenPackageFileContent['assets'] as $pattern) {
                        $fileNames = glob($packageDir.'/'.$pattern);

                        $totalOfFiles += count($fileNames);

                        foreach ($fileNames as $fileName) {
                            $totalSize += filesize($fileName);
                        }
                    }

                    $tableRows[] = [$packageName, $totalOfFiles, $this->bytesToHuman($totalSize)];
                }
            }
        }

        $table = new Table($output);
        $table->setHeaders(['Package', 'Total of files', 'Total size']);
        $table->setRows($tableRows);
        $table->render();

        return Command::SUCCESS;
    }

    public function bytesToHuman(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
