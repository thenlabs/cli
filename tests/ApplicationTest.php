<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use ThenLabs\Cli\Helpers;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

define('THEN_COMMANDS', [
    'kit:list:installed',
    'kit:install',
]);

define('PROJECT_DIR', __DIR__.'/project');
define('TEMP_DIR', __DIR__.'/temp');

testCase('ApplicationTest.php', function () {
    testCase('using the real file system', function () {
        setUpBeforeClass(function () {
            static::createTempDir();

            $application = new Application;
            static::setVar('application', $application);
        });

        createStaticMethod('createTempDir', function () {
            static::removeTempDir();
            Helpers::copyDirectory(PROJECT_DIR, TEMP_DIR);
        });

        createStaticMethod('removeTempDir', function () {
            if (is_dir(TEMP_DIR)) {
                Helpers::removeDirectory(TEMP_DIR);
            }
        });

        testCase('runs the kit:install command', function () {
            setUpBeforeClass(function () {
                $application = static::getVar('application');
                $command = $application->find('kit:install');
                $arguments = ['directory' => PROJECT_DIR];

                $commandTester = new CommandTester($command);
                $commandTester->execute($arguments);

                $output = $commandTester->getDisplay();
                $tempDir = static::readDirectoryInArray(TEMP_DIR);

                static::addVars(compact('output', 'tempDir'));
            });

            test('all files from vendor1/package11 they are copied', function () {
                $this->assertEquals(
                    $this->tempDir['vendor']['vendor1']['package11']['assets'],
                    $this->tempDir['public']['vendor1']['package11']
                );
            });

            // test('all files from vendor2/package21 they are copied', function () {
            //     $this->assertEquals(
            //         $this->structure2[$this->rootDirName]['vendor']['vendor2']['package21']['resources'],
            //         $this->structure2[$this->rootDirName]['public']['vendor2']['package21']
            //     );
            // });

            // test('the file1.json has been created and merged successfull', function () {
            //     $expectedContent = [
            //         'data1' => 'value1',
            //         'data2' => 'value2',
            //         'data3' => 'value3',
            //         'data4' => 'value4',
            //         'data5' => 'value5',
            //         'data6' => 'value6',
            //     ];

            //     $this->assertEquals(
            //         $expectedContent,
            //         json_decode($this->structure2[$this->rootDirName]['public']['file1.json'], true)
            //     );
            // });
        });

        tearDownAfterClass(function () {
            static::removeTempDir();
        });
    });

    testCase('using a virtual file system', function () {
        testCase('exists a directory', function () {
            setUp(function () {
                $this->application = new Application;
                vfsStreamWrapper::register();

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

                    $this->assertContains('The composer.lock file is missing.', $this->output);
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

                        $this->assertContains('The composer.lock file is corrupt.', $this->output);
                    });
                }
            });

            testCase('exists a composer.lock file that contains two thenkits', function () {
                setUp(function () {
                    $this->composerLockContent = [
                        'packages' => [
                            [
                                'name' => uniqid('kit'),
                                'type' => 'thenkit',
                            ],
                            [
                                'name' => uniqid('library'),
                                'type' => 'library',
                            ],
                            [
                                'name' => uniqid('kit'),
                                'type' => 'thenkit',
                            ],
                        ]
                    ];

                    $this->composerLockFile = new vfsStreamFile('composer.lock');
                    $this->composerLockFile->setContent(json_encode($this->composerLockContent));

                    $this->rootDir->addChild($this->composerLockFile);
                });

                test('the command "kit:list:installed" shows the expected kits', function () {
                    $this->runCommand('kit:list:installed', []);

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

                test('the command "kit:install" shows message The "then.json" file is missing.', function () {
                    $this->runCommand('kit:install', []);

                    $this->assertContains('The "then.json" file is missing.', $this->output);
                });

                testCase('exists an invalid then.json file', function () {
                    setUp(function () {
                        $this->thenJsonFile = new vfsStreamFile('then.json');
                        $this->thenJsonFile->setContent(uniqid());

                        $this->rootDir->addChild($this->thenJsonFile);
                    });

                    test('the command "kit:install" shows message The "then.json" file is corrupt.', function () {
                        $this->runCommand('kit:install', []);

                        $this->assertContains('The "then.json" file is corrupt.', $this->output);
                    });
                });
            });

            // testCase('exists a project', function () {
            //     setUp(function () {
            //         vfsStream::copyFromFileSystem(__DIR__.'/project');

            //         $this->structure1 = $this->getStructure();
            //     });

            //     createMethod('getStructure', function () {
            //         return vfsStream::inspect(new vfsStreamStructureVisitor)->getStructure();
            //     });

            //     testCase('runs the kit:install command', function () {
            //         setUp(function () {
            //             $this->runCommand('kit:install', []);

            //             $this->structure2 = $this->getStructure();
            //         });

            //         test('all files from vendor1/package11 they are copied', function () {
            //             $this->assertEquals(
            //                 $this->structure2[$this->rootDirName]['vendor']['vendor1']['package11']['assets'],
            //                 $this->structure2[$this->rootDirName]['public']['vendor1']['package11']
            //             );
            //         });

            //         test('all files from vendor2/package21 they are copied', function () {
            //             $this->assertEquals(
            //                 $this->structure2[$this->rootDirName]['vendor']['vendor2']['package21']['resources'],
            //                 $this->structure2[$this->rootDirName]['public']['vendor2']['package21']
            //             );
            //         });

            //         test('the file1.json has been created and merged successfull', function () {
            //             $expectedContent = [
            //                 'data1' => 'value1',
            //                 'data2' => 'value2',
            //                 'data3' => 'value3',
            //                 'data4' => 'value4',
            //                 'data5' => 'value5',
            //                 'data6' => 'value6',
            //             ];

            //             $this->assertEquals(
            //                 $expectedContent,
            //                 json_decode($this->structure2[$this->rootDirName]['public']['file1.json'], true)
            //             );
            //         });
            //     });
            // });
        });
    });
});
