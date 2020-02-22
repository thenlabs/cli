<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Kit;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends ThenCommand
{
    protected static $defaultName = 'kit:install';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Install the assets files of the installed kits');

        $this->addArgument('thenkit-file', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');
        $installedKits = $this->getInstalledKits($directory, $output);
        $thenkitFile = $input->getArgument('thenkit-file');

        $thenJson = $this->getThenJson($directory, $output);
        if (! isset($thenJson->targetAssetsDir)) {
            return 0;
        }

        if ($thenkitFile) {
            if (! file_exists($thenkitFile)) {
                $output->writeln('The specified "thenkit" file not exists.');
                return 0;
            }

            $path = pathinfo($thenkitFile);
            $thenKitDir = $path['dirname'];
            $composerJsonFile = $thenKitDir.'/composer.json';
            $composerJson = json_decode(file_get_contents($composerJsonFile));

            $this->installKit($directory, $thenJson, $thenKitDir, $composerJson->name);

            return 0;
        }

        foreach ($installedKits as $kitName) {
            $kitDir = "{$directory}/vendor/{$kitName}";
            $this->installKit($directory, $thenJson, $kitDir, $kitName);
        }

        return 0;
    }

    private function installKit(string $directory, object $thenJson, string $kitDir, string $kitName): void
    {
        $filesystem = new Filesystem;
        $targetAssetsDir = $directory.'/'.$thenJson->targetAssetsDir.'/'.$kitName;

        if (! is_dir($targetAssetsDir)) {
            mkdir($targetAssetsDir, 0777, true);
        }

        $thenKit = json_decode(file_get_contents($kitDir.'/thenkit.json'));
        if (! is_object($thenKit)) {
            return;
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
}
