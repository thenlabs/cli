<?php

namespace ThenLabs\Cli\Command\Assets;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InstallCommand extends Command
{
    protected static $defaultName = 'assets:install';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $application = $this->getApplication();
            $workingDirectory = $application->getWorkingDirectory();
            $filesystem = new Filesystem();

            $thenJsonFilename = $workingDirectory.'/then.json';
            $thenJson = json_decode(file_get_contents($thenJsonFilename), true);

            if (! is_array($thenJson)) {
                $output->writeln("The file '{$thenJsonFilename}' not contains a valid JSON.");
                return Command::FAILURE;
            }

            $targetAssetsDir = $workingDirectory.'/'.$thenJson['targetAssetsDir'];

            $fileList = ListCommand::getFileList($workingDirectory);

            foreach ($fileList as $packageName => $fileNames) {
                $targetPackageDir = $targetAssetsDir.'/'.$packageName;

                if ($filesystem->exists($targetPackageDir)) {
                    $filesystem->remove($targetPackageDir);
                }

                $filesystem->mkdir($targetPackageDir);

                $packageDir = "{$workingDirectory}/vendor/{$packageName}";
                $thenPackageFilename = "{$packageDir}/then-package.json";
                $thenPackageFileContent = json_decode(file_get_contents($thenPackageFilename), true);

                if (! is_array($thenPackageFileContent)) {
                    $output->writeln("The file '{$thenPackageFilename}' not contains a valid JSON.");
                    continue;
                }

                if (isset($thenPackageFileContent['beforeCopy']) &&
                    is_array($thenPackageFileContent['beforeCopy'])
                ) {
                    foreach ($thenPackageFileContent['beforeCopy'] as $script) {
                        $output->writeln("Running script '{$script}' for the package '{$packageName}'.");
                        $this->runScript($script, $targetPackageDir, $output);
                    }
                }

                $output->writeln("Copying files of the package '{$packageName}'.");
                $filesystem->mirror(
                    $packageDir.'/'.$thenPackageFileContent['assetsDir'],
                    $targetPackageDir
                );

                if (isset($thenPackageFileContent['afterCopy']) &&
                    is_array($thenPackageFileContent['afterCopy'])
                ) {
                    foreach ($thenPackageFileContent['afterCopy'] as $script) {
                        $output->writeln("Running script '{$script}' for the package '{$packageName}'.");
                        $this->runScript($script, $targetPackageDir, $output);
                    }
                }
            }

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    }

    private function runScript(string $script, string $cwd, $output): void
    {
        $parts = explode(' ', $script);
        $process = new Process($parts, $cwd);

        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write('ERROR > '.$buffer);
            } else {
                $output->write($buffer);
            }
        });

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
