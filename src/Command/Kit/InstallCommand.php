<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Kit;

use ThenLabs\Cli\Helpers;
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
        $filesystem = new Filesystem();

        foreach ($installedKits as $kit) {
            $kitDir = "{$directory}/vendor/{$kit}";
            $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir.'/'.$kit;

            if (! is_dir($targetAssetsDir)) {
                mkdir($targetAssetsDir, 0777, true);
            }

            $thenKit = json_decode(file_get_contents($kitDir.'/thenkit.json'));

            if (is_object($thenKit) &&
                isset($thenKit->assets) &&
                is_object($thenKit->assets)
            ) {
                foreach ($thenKit->assets as $key => $value) {
                    $targetDir = $targetAssetsDir;

                    $matches = glob($kitDir.'/'.$key);

                    foreach ($matches as $filename) {
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

            if (isset($thenKit->merge) && is_array($thenKit->merge)) {
                foreach ($thenKit->merge as $filename) {
                    $sourceFilename = $kitDir.'/'.$filename;
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
                                        json_encode(array_merge($currentContent, $newContent), JSON_PRETTY_PRINT)
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
