<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ApplicationTest.php', function () {
    setUp(function () {
        vfsStreamWrapper::register();

        $this->rootDirName = uniqid('dir');
        $this->rootDir = new vfsStreamDirectory($this->rootDirName);

        vfsStreamWrapper::setRoot($this->rootDir);

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

        testCase('exists an invalid composer.lock file', function () {
            setUp(function () {
                $this->composerLockFile = new vfsStreamFile('composer.lock');
                $this->composerLockFile->setContent(uniqid());

                $this->rootDir->addChild($this->composerLockFile);
            });

            test('shows error message when the composer.lock file is invalid', function () {
                $this->commandTester->execute([
                    'directory' => vfsStream::url($this->rootDirName)
                ]);

                $output = $this->commandTester->getDisplay();

                $this->assertContains('the composer.lock file is corrupt.', $output);
            });
        });

        testCase('exists a composer.lock file that contains two thenlabs packages', function () {
            setUp(function () {
                $this->composerLockContent = [
                    'packages' => [
                        [
                            'name' => uniqid('package'),
                            'type' => 'thenlabs-package',
                        ],
                        [
                            'name' => uniqid('library'),
                            'type' => 'library',
                        ],
                        [
                            'name' => uniqid('package'),
                            'type' => 'thenlabs-package',
                        ],
                    ]
                ];

                $this->composerLockFile = new vfsStreamFile('composer.lock');
                $this->composerLockFile->setContent(json_encode($this->composerLockContent));

                $this->rootDir->addChild($this->composerLockFile);
            });

            test('prints the expected packages', function () {
                $this->commandTester->execute([
                    'directory' => vfsStream::url($this->rootDirName)
                ]);

                $output = $this->commandTester->getDisplay();

                $this->assertContains(
                    $this->composerLockContent['packages'][0]['name'],
                    $output
                );
                $this->assertNotContains(
                    $this->composerLockContent['packages'][1]['name'],
                    $output
                );
                $this->assertContains(
                    $this->composerLockContent['packages'][2]['name'],
                    $output
                );
            });
        });
    });
});
