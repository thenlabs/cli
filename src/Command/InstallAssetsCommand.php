<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        foreach ($installedPackages as $package) {
            $packageDir = "{$directory}/vendor/{$package}";
            $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir.'/'.$package;

            if (! is_dir($targetAssetsDir)) {
                mkdir($targetAssetsDir, 0777, true);
            }

            $thenPackage = json_decode(file_get_contents($packageDir.'/then-package.json'));

            if (isset($thenPackage->assetsDir) && is_array($thenPackage->assetsDir)) {
                foreach ($thenPackage->assetsDir as $assetsDir) {
                    $packageAssetsDir = $packageDir.'/'.$assetsDir;

                    $this->copyDirectory($packageAssetsDir, $targetAssetsDir);
                }
            }

            if (isset($thenPackage->merge) && is_array($thenPackage->merge)) {
                foreach ($thenPackage->merge as $filename) {
                    $sourceFilename = $packageDir.'/'.$filename;
                    $targetFilename = $directory.'/'.$thenJson->targetAssetsDir.'/'.basename($filename);

                    if (file_exists($targetFilename)) {
                        $pathInfo = pathInfo($targetFilename);

                        switch ($pathInfo['extension']) {
                            case 'json':
                                $currentContent = json_decode(file_get_contents($targetFilename), true);
                                $newContent = json_decode(file_get_contents($sourceFilename), true);

                                if (is_array($currentContent) && is_array($newContent)) {
                                    file_put_contents(
                                        $targetFilename,
                                        json_encode(array_merge($currentContent, $newContent),  JSON_PRETTY_PRINT)
                                    );
                                }
                                break;
                        }
                    } else {
                        copy($sourceFilename, $targetFilename);
                    }
                }
            }
        }

        return 0;
    }
}
