<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ThenCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('directory', InputArgument::OPTIONAL, '', getcwd());
    }

    protected function getInstalledKits(InputInterface $input, OutputInterface $output): array
    {
        $composerLockFile = $input->getArgument('directory') . '/composer.lock';
        $installedKits = [];

        if (file_exists($composerLockFile)) {
            $content = json_decode(file_get_contents($composerLockFile));
            if (is_object($content)) {
                foreach ($content->packages as $package) {
                    if ($package->type == 'thenkit') {
                        $installedKits[] = $package->name;
                    }
                }
            } else {
                $output->writeln('The composer.lock file is corrupt.');
            }
        } else {
            $output->writeln('The composer.lock file is missing.');
        }

        return $installedKits;
    }

    protected function getThenJson(InputInterface $input, OutputInterface $output)
    {
        $thenJsonFile = $input->getArgument('directory') . '/then.json';

        if (! file_exists($thenJsonFile)) {
            $output->writeln('The "then.json" file is missing.');
            return;
        }

        $thenJson = json_decode(file_get_contents($thenJsonFile));

        if (! is_object($thenJson)) {
            $output->writeln('The "then.json" file is corrupt.');
        }

        return $thenJson;
    }
}
