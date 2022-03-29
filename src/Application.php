<?php
declare(strict_types=1);

namespace ThenLabs\Cli;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Application extends SymfonyApplication
{
    /**
     * @var string
     */
    protected $workingDirectory;

    public function __construct()
    {
        parent::__construct();

        $this->add(new Command\Assets\ListCommand);
        $this->add(new Command\Assets\InstallCommand);

        $this->workingDirectory = getcwd();
    }

    public function setWorkingDirectory(string $workingDirectory): void
    {
        $this->workingDirectory = $workingDirectory;
    }

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }
}
