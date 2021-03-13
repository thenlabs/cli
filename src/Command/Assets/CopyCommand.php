<?php

namespace ThenLabs\Cli\Command\Assets;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class CopyCommand extends Command
{
    protected static $defaultName = 'assets:copy';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $workingDirectory = $application->getWorkingDirectory();

        try {
            $fileList = ListCommand::getFileList($workingDirectory);

            foreach ($fileList as $packageName => $fileNames) {
            }

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
