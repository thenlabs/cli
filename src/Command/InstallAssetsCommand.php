<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

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

        $this->setDescription('Install the assets of the installed packages');

        $this->addArgument('then-package-file', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');
        $installedPackages = $this->getInstalledPackages($directory, $output);
        $thenPackageFile = $input->getArgument('then-package-file');

        $thenJson = $this->getThenJson($directory, $output);
        if (! isset($thenJson->targetAssetsDir)) {
            return 0;
        }

        if ($thenPackageFile) {
            if (! file_exists($thenPackageFile)) {
                $output->writeln('The specified then package file not exists.');
                return 0;
            }

            $path = pathinfo($thenPackageFile);
            $thenPackageDir = $path['dirname'];
            $composerJsonFile = $thenPackageDir.'/composer.json';
            $composerJson = json_decode(file_get_contents($composerJsonFile));

            $this->installPackage($directory, $thenJson, $thenPackageDir, $composerJson->name);

            return 0;
        }

        foreach ($installedPackages as $packageName) {
            $packageDir = "{$directory}/vendor/{$packageName}";
            $this->installPackage($directory, $thenJson, $packageDir, $packageName);
        }

        return 0;
    }

    private function installPackage(string $directory, object $thenJson, string $packageDir, string $packageName): void
    {
        $filesystem = new Filesystem;
        $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir.'/'.$packageName;

        if (! is_dir($targetAssetsDir)) {
            mkdir($targetAssetsDir, 0777, true);
        }

        $thenPackage = json_decode(file_get_contents($packageDir.'/then-package.json'));
        if (! is_object($thenPackage)) {
            return;
        }

        if (isset($thenPackage->assets) && is_object($thenPackage->assets)) {
            foreach ($thenPackage->assets as $key => $value) {
                $targetDir = $targetAssetsDir;

                foreach (glob($packageDir.'/'.$key) as $filename) {
                    $newFilename = $targetDir.'/';
                    $newFilename .= $value ? $value : basename($filename);

                    if (is_dir($filename)) {
                        $filesystem->mirror($filename, $newFilename);
                    } elseif (is_file($filename)) {
                        $filesystem->copy($filename, $newFilename);
                    }
                }
            }
        }

        if (isset($thenPackage->mergeJson) && is_object($thenPackage->mergeJson)) {
            foreach ($thenPackage->mergeJson as $baseFilename => $options) {
                $sourceFilename = $packageDir.'/'.$baseFilename;
                $targetFilename = $directory.'/'.$thenJson->targetAssetsDir.'/'.$options->target;

                $sourceContent = json_decode(file_get_contents($sourceFilename), true);
                $content = [];

                foreach ($options->keys as $key) {
                    if (isset($sourceContent[$key])) {
                        $content[$key] = $sourceContent[$key];
                    }
                }

                if (file_exists($targetFilename)) {
                    $targetContent = json_decode(file_get_contents($targetFilename), true);
                    $content = array_merge_recursive($content, $targetContent);
                }

                file_put_contents($targetFilename, json_encode($content, JSON_PRETTY_PRINT));
            }
        }
    }
}
