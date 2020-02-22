<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Kit;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends ThenCommand
{
    protected static $defaultName = 'kit:install';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Install the assets files of the installed kits');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installedKits = $this->getInstalledKits($input, $output);
        $thenJson = $this->getThenJson($input, $output);

        if (! isset($thenJson->targetAssetsDir)) {
            return 0;
        }

        $directory = $input->getArgument('directory');
        $filesystem = new Filesystem;

        foreach ($installedKits as $kit) {
            $kitDir = "{$directory}/vendor/{$kit}";
            $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir.'/'.$kit;

            if (! is_dir($targetAssetsDir)) {
                mkdir($targetAssetsDir, 0777, true);
            }

            $thenKit = json_decode(file_get_contents($kitDir.'/thenkit.json'));
            if (! is_object($thenKit)) {
                continue;
            }

            if (isset($thenKit->assets) && is_object($thenKit->assets)) {
                foreach ($thenKit->assets as $key => $value) {
                    $targetDir = $targetAssetsDir;

                    foreach (glob($kitDir.'/'.$key) as $filename) {
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

            if (isset($thenKit->mergeJson) && is_object($thenKit->mergeJson)) {
                foreach ($thenKit->mergeJson as $baseFilename => $options) {
                    $sourceFilename = $kitDir.'/'.$baseFilename;
                    $targetFilename = $directory.'/'.$thenJson->targetAssetsDir.'/'.$options->target;

                    $sourceContent = json_decode(file_get_contents($sourceFilename), true);
                    $content = [];

                    foreach ($options->keys as $key) {
                        $content[$key] = $sourceContent[$key];
                    }

                    if (file_exists($targetFilename)) {
                        $targetContent = json_decode(file_get_contents($targetFilename), true);
                        $content = array_merge_recursive($content, $targetContent);
                    }

                    file_put_contents($targetFilename, json_encode($content));
                }
            }
        }

        return 0;
    }
}
