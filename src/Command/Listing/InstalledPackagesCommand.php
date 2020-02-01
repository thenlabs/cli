<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Listing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class InstalledPackagesCommand extends Command
{
    protected static $defaultName = 'list:installed-packages';

    protected function configure()
    {
        $this
            ->setDescription('Lists all the installed packages that type is "thenlabs-package".')
            ->addArgument('directory', InputArgument::OPTIONAL, '', getcwd())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerLockFile = $input->getArgument('directory') . '/composer.lock';

        if (! file_exists($composerLockFile)) {
            $output->writeln('the composer.lock file is missing.');
            return 0;
        }

        $content = json_decode(file_get_contents($composerLockFile));
        if (! is_object($content)) {
            $output->writeln('the composer.lock file is corrupt.');
            return 0;
        }

        foreach ($content->packages as $package) {
            if ($package->type == 'thenlabs-package') {
                $output->writeln($package->name);
            }
        }

        return 0;
    }
}
