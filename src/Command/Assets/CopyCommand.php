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
            $targetDir = $workingDirectory.'/'.$thenJson['assets']['targetDir'];

            $fileList = ListCommand::getFileList($workingDirectory);

            foreach ($fileList as $packageName => $fileNames) {
                $targetPackageDir = $targetDir.'/'.$packageName;

                if (! $filesystem->exists($targetPackageDir)) {
                    $filesystem->mkdir($targetPackageDir);
                }

                foreach ($fileNames as $fileName) {
                    $targetFileName = $targetPackageDir.'/'.basename($fileName);
                    $filesystem->copy($fileName, $targetFileName);

                    $output->writeln($targetFileName);
                }
            }

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
