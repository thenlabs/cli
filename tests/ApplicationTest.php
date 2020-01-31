<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamWrapper;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ApplicationTest.php', function () {
    setUp(function () {
        vfsStreamWrapper::register();

        $this->application = new Application;
    });

    testCase('list:installed-packages', function () {
        setUp(function () {
            $this->command = $this->application->find('list:installed-packages');
            $this->commandTester = new CommandTester($this->command);
        });

        test('shows error message when the composer.lock file is missing', function () {
            $this->commandTester->execute([]);

            $output = $this->commandTester->getDisplay();

            $this->assertContains('the composer.lock file is missing.', $output);
        });

        // setUp(function () {
        //     vfsStreamWrapper::register();

        //     $this->composerLockContent = [
        //         'packages' => [
        //         ]
        //     ];

        //     $composerLock = vfsStream::newFile('composer.lock');
        //     $composerLock->setContent($this->composerLockContent);

        //     $this->application = new Application;
        // });

        // test('name of the test', function () {
        //     $command = $this->application->find('list:installed-packages');
        //     $commandTester = new CommandTester($command);
        //     $commandTester->execute([]);

        //     $output = $commandTester->getDisplay();
        //     // $this->assertContains('Username: Wouter', $output);
        // });
    });
});
