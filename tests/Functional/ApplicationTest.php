<?php

namespace ThenLabs\Cli\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use ThenLabs\Cli\Application;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('ApplicationTest.php', function () {
    setUpBeforeClass(function () {
        $filesystem = new Filesystem;

        if ($filesystem->exists(TEMP_PROJECT_DIR)) {
            $filesystem->remove(TEMP_PROJECT_DIR);
        }

        $filesystem->mirror(PROJECT_DIR, TEMP_PROJECT_DIR);

        $application = new Application;
        $application->setWorkingDirectory(TEMP_PROJECT_DIR);

        static::setVar('application', $application);
    });

    testCase(function () {
        setUpBeforeClass(function () {
            $application = static::getVar('application');
            $command = $application->find('assets:list');

            $commandTester = new CommandTester($command);
            $commandTester->execute([]);

            $output = $commandTester->getDisplay();

            static::setVar('output', $output);
        });

        test(function () {
            $this->assertContains('vendor1/package1', $this->output);
        });
    });
});
