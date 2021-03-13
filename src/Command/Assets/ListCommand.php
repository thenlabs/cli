<?php

namespace ThenLabs\Cli\Command\Assets;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;
use Exception;

class ListCommand extends Command
{
    protected static $defaultName = 'assets:list';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $workingDirectory = $application->getWorkingDirectory();

        try {
            $fileList = self::getFileList($workingDirectory);

            $table = new Table($output);
            $table->setHeaders(['Package', 'Total of files', 'Total size']);

            foreach ($fileList as $packageName => $fileNames) {
                $totalOfFiles = count($fileNames);
                $totalSize = 0;

                foreach ($fileNames as $fileName) {
                    $totalSize += filesize($fileName);
                }

                $table->addRow([$packageName, $totalOfFiles, $totalSize]);
            }

            $table->render();
            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    }

    public function bytesToHuman(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function getFileList(string $workingDirectory): array
    {
        $result = [];

        $composerLockFile = $workingDirectory.'/composer.lock';

        if (! file_exists($composerLockFile)) {
            throw new Exception('The "composer.lock" file is missing.');
        }

        $composerLockFileContent = json_decode(file_get_contents($composerLockFile), true);

        if (! is_array($composerLockFileContent)) {
            throw new Exception('The "composer.lock" file is corrupt.');
        }

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
                    $result[$packageName] = [];

                    foreach ($thenPackageFileContent['assets'] as $pattern) {
                        $fileNames = glob($packageDir.'/'.$pattern);

                        foreach ($fileNames as $fileName) {
                            $result[$packageName][] = $fileName;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
