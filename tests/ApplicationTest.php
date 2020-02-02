<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

define('THEN_COMMANDS', [
    'list:installed-packages',
    'install:assets',
]);

testCase('ApplicationTest.php', function () {
    setUp(function () {
        vfsStreamWrapper::register();

        $this->application = new Application;
    });

    testCase('exists a directory', function () {
        setUp(function () {
            $this->rootDirName = uniqid('dir');
            $this->rootDir = new vfsStreamDirectory($this->rootDirName);

            vfsStreamWrapper::setRoot($this->rootDir);
        });

        createMethod('runCommand', function (string $command, array $arguments) {
            $command = $this->application->find($command);
            $defaults = ['directory' => vfsStream::url($this->rootDirName)];
            $arguments = array_merge($defaults, $arguments);

            $commandTester = new CommandTester($command);
            $commandTester->execute($arguments);

            $this->output = $commandTester->getDisplay();
        });

        foreach (THEN_COMMANDS as $command) {
            test("the commmand '{$command}' shows error message when the composer.lock file is missing", function () use ($command) {
                $this->runCommand($command, []);

                $this->assertContains('the composer.lock file is missing.', $this->output);
            });
        }

        testCase('exists an invalid composer.lock file', function () {
            setUp(function () {
                $this->composerLockFile = new vfsStreamFile('composer.lock');
                $this->composerLockFile->setContent(uniqid());

                $this->rootDir->addChild($this->composerLockFile);
            });

            foreach (THEN_COMMANDS as $command) {
                test("the command '{$command}' shows error message when the composer.lock file is invalid", function () use ($command) {
                    $this->runCommand($command, []);

                    $this->assertContains('the composer.lock file is corrupt.', $this->output);
                });
            }
        });

        testCase('exists a composer.lock file that contains two then packages', function () {
            setUp(function () {
                $this->composerLockContent = [
                    'packages' => [
                        [
                            'name' => uniqid('package'),
                            'type' => 'then-package',
                        ],
                        [
                            'name' => uniqid('library'),
                            'type' => 'library',
                        ],
                        [
                            'name' => uniqid('package'),
                            'type' => 'then-package',
                        ],
                    ]
                ];

                $this->composerLockFile = new vfsStreamFile('composer.lock');
                $this->composerLockFile->setContent(json_encode($this->composerLockContent));

                $this->rootDir->addChild($this->composerLockFile);
            });

            test('the command "list:installed-packages" shows the expected packages', function () {
                $this->runCommand('list:installed-packages', []);

                $this->assertContains(
                    $this->composerLockContent['packages'][0]['name'],
                    $this->output
                );
                $this->assertNotContains(
                    $this->composerLockContent['packages'][1]['name'],
                    $this->output
                );
                $this->assertContains(
                    $this->composerLockContent['packages'][2]['name'],
                    $this->output
                );
            });
        });

        testCase('exists a thenlabs package', function () {
            setUp(function () {
                $this->targetDir = uniqid('assets');
                $this->assetsDirOfThePackage = uniqid('assets');

                $this->vendorName = uniqid('vendor');
                $this->packageName = uniqid('package');

                $this->file1 = uniqid('file1');
                $this->file2 = uniqid('file2');
                $this->dir1 = uniqid('dir1');

                $structure = [
                    $this->targetDir => [],
                    'vendor' => [
                        $this->vendorName => [
                            $this->packageName => [
                                $this->assetsDirOfThePackage = [
                                    $this->file1,
                                    $this->dir1 => [
                                        $this->file1
                                    ]
                                ],
                                'then-package.json' => json_encode([
                                    'assets' => [
                                        $this->assetsDirOfThePackage,
                                    ]
                                ])
                            ]
                        ],
                    ],
                    'then.json' => json_encode([
                        'targetAssetsDir' => $this->targetDir
                    ]),
                ];

                vfsStream::setup('root', null, $structure);
            });

            testCase('runs the install:assets command', function () {
                setUp(function () {
                    $this->runCommand('install:assets', []);
                });

                test('name of the test', function () {
                    $this->assertContains('Installation success.', $this->output);
                });
            });
        });
    });
});
