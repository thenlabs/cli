<?php

namespace ThenLabs\Cli\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use ThenLabs\Cli\Application;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('ApplicationTest.php', function () {
    // setUpBeforeClass(function () {
    //     $filesystem = new Filesystem;

    //     if ($filesystem->exists(TEMP_PROJECT_DIR)) {
    //         $filesystem->remove(TEMP_PROJECT_DIR);
    //     }

    //     $filesystem->mirror(PROJECT_DIR, TEMP_PROJECT_DIR);

    //     static::setVar('application', new Application);
    // });

    // testCase(function () {
    //     setUpBeforeClass(function () {
    //         $application = static::getVar('application');
    //         $command = $application->find('assets:list-packages');

    //         $commandTester = new CommandTester($command);
    //         $commandTester->execute($arguments);
    //     });
    // });
});
