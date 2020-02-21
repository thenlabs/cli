<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Command\Kit\List_;

use ThenLabs\Cli\Command\ThenCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class InstalledCommand extends ThenCommand
{
    protected static $defaultName = 'kit:list:installed';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Lists all the installed then kits.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getInstalledKits($input, $output) as $kit) {
            $output->writeln($kit);
        }

        return 0;
    }
}
