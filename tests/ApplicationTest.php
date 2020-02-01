<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ApplicationTest.php', function () {
    setUp(function () {
        $this->rootDirName = uniqid('dir');
        $this->rootDir = vfsStream::setup($this->rootDirName);

        $this->application = new Application;
    });

    testCase('list:installed-packages', function () {
        setUp(function () {
            $this->command = $this->application->find('list:installed-packages');
            $this->commandTester = new CommandTester($this->command);
        });

        test('shows error message when the composer.lock file is missing', function () {
            $this->commandTester->execute([
                'directory' => vfsStream::url($this->rootDirName)
            ]);

            $output = $this->commandTester->getDisplay();

            $this->assertContains('the composer.lock file is missing.', $output);
        });

        // testCase('exists a composer.lock file that contains two thenlabs packages', function () {
        //     setUp(function () {
        //         $this->composerLockContent = [
        //             'packages' => [
        //                 [
        //                     'name' => uniqid('package'),
        //                     'type' => 'thenlabs-package',
        //                 ],
        //                 [
        //                     'name' => uniqid('library'),
        //                     'type' => 'library',
        //                 ],
        //                 [
        //                     'name' => uniqid('package'),
        //                     'type' => 'thenlabs-package',
        //                 ],
        //             ]
        //         ];

        //         $this->composerLock = vfsStream::newFile('composer.lock');
        //         $this->composerLock->setContent(json_encode($this->composerLockContent));
        //     });

        //     test('', function () {
        //         $this->commandTester->execute([]);

        //         $output = $this->commandTester->getDisplay();

        //         $this->assertContains(
        //             $this->composerLockContent['packages'][0]['name'],
        //             $output
        //         );
        //         $this->assertNotContains(
        //             $this->composerLockContent['packages'][1]['name'],
        //             $output
        //         );
        //         $this->assertContains(
        //             $this->composerLockContent['packages'][2]['name'],
        //             $output
        //         );
        //     });
        // });

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
