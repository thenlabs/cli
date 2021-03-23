<?php

namespace ThenLabs\Cli\Command\Assets;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class CopyCommand extends Command
{
    protected static $defaultName = 'assets:copy';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $application = $this->getApplication();
            $workingDirectory = $application->getWorkingDirectory();
            $filesystem = new Filesystem;

            $thenJson = json_decode(file_get_contents($workingDirectory.'/then.json'), true);
            $targetAssetsDir = $workingDirectory.'/'.$thenJson['targetAssetsDir'];

            $fileList = ListCommand::getFileList($workingDirectory);

            foreach ($fileList as $packageName => $fileNames) {
                $targetPackageDir = $targetAssetsDir.'/'.$packageName;

                $packageDir = "{$workingDirectory}/vendor/{$packageName}";
                $thenPackageFile = "{$packageDir}/then-package.json";
                $thenPackageFileContent = json_decode(file_get_contents($thenPackageFile), true);

                $filesystem->mirror(
                    $packageDir.'/'.$thenPackageFileContent['assetsDir'],
                    $targetPackageDir
                );
            }

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
