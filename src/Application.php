<?php
declare(strict_types=1);

namespace ThenLabs\Cli;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new Command\Listing\InstalledPackagesCommand);
        $this->add(new Command\Install\AssetsCommand);
    }
}
