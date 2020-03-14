<?php

namespace ThenLabs\Cli\Tests;

use ThenLabs\Cli\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

define('THEN_COMMANDS', [
    'kit:list:installed',
    'kit:install',
]);

define('PROJECT_DIR', __DIR__.'/project');
define('TEMP_DIR', __DIR__.'/temp');
define('PROJECT_DIR2', __DIR__.'/thenkit-project');
define('TEMP_DIR2', __DIR__.'/temp2');

testCase('ApplicationTest.php', function () {
    // testCase('using the real file system', function () {
    //     setUpBeforeClass(function () {
    //         static::createTempDir();

    //         $application = new Application;
    //         static::setVar('application', $application);
    //     });

    //     createStaticMethod('createTempDir', function () {
    //         static::removeTempDir();

    //         $filesystem = new Filesystem();
    //         $filesystem->mirror(PROJECT_DIR, TEMP_DIR);
    //         $filesystem->mirror(PROJECT_DIR2, TEMP_DIR2);
    //     });

    //     createStaticMethod('removeTempDir', function () {
    //         if (is_dir(TEMP_DIR)) {
    //             $filesystem = new Filesystem();
    //             $filesystem->remove(TEMP_DIR);
    //         }
    //         if (is_dir(TEMP_DIR2)) {
    //             $filesystem = new Filesystem();
    //             $filesystem->remove(TEMP_DIR2);
    //         }
    //     });

    //     testCase('runs the kit:install command', function () {
    //         setUpBeforeClass(function () {
    //             $application = static::getVar('application');
    //             $command = $application->find('kit:install');
    //             $arguments = ['directory' => TEMP_DIR];

    //             $commandTester = new CommandTester($command);
    //             $commandTester->execute($arguments);

    //             $output = $commandTester->getDisplay();
    //             $tempDir = static::readDirectoryInArray(TEMP_DIR);

    //             static::addVars(compact('output', 'tempDir'));
    //         });

    //         test('all files from vendor1/package11 they are copied', function () {
    //             $this->assertEquals(
    //                 $this->tempDir['vendor']['vendor1']['package11']['assets'],
    //                 $this->tempDir['public']['vendor1']['package11']
    //             );
    //         });

    //         test('all files from vendor2/package21 they are copied', function () {
    //             $this->assertEquals(
    //                 $this->tempDir['vendor']['vendor2']['package21']['resources']['file1.txt'],
    //                 $this->tempDir['public']['vendor2']['package21']['file1.txt']
    //             );
    //             $this->assertEquals(
    //                 $this->tempDir['vendor']['vendor2']['package21']['resources']['file2.txt'],
    //                 $this->tempDir['public']['vendor2']['package21']['newDir']['newFile2.txt']
    //             );
    //             $this->assertFalse(
    //                 isset($this->tempDir['public']['vendor2']['package21']['dir'])
    //             );
    //             $this->assertFalse(
    //                 isset($this->tempDir['public']['vendor2']['package21']['dir']['file222.txt'])
    //             );
    //         });

    //         test('the file1.json has been merged successfull', function () {
    //             $expectedContent = [
    //                 'dependencies' => [
    //                     'dep1' => 'version1',
    //                     'dep2' => 'version2',
    //                     'dep3' => 'version3',
    //                     'dep4' => 'version4',
    //                     'dep5' => 'version5',
    //                 ],
    //                 'devDependencies' => [
    //                     'devDep1' => 'v1',
    //                     'devDep2' => 'v2',
    //                     'devDep3' => 'v3',
    //                     'devDep4' => 'v4',
    //                 ],
    //             ];

    //             $current = json_decode($this->tempDir['public']['file1.json'], true);

    //             $this->assertEquals($expectedContent, $current);
    //         });
    //     });

    //     testCase('runs the kit:install command specifying the argument "thenkit-file"', function () {
    //         setUpBeforeClass(function () {
    //             $application = static::getVar('application');
    //             $command = $application->find('kit:install');
    //             $arguments = [
    //                 'directory' => TEMP_DIR2.'/examples',
    //                 'thenkit-file' => TEMP_DIR2.'/thenkit.json',
    //             ];

    //             $commandTester = new CommandTester($command);
    //             $commandTester->execute($arguments);

    //             $output = $commandTester->getDisplay();
    //             $tempDir = static::readDirectoryInArray(TEMP_DIR2);

    //             static::addVars(compact('output', 'tempDir'));
    //         });

    //         test('all files from assets folder they are copied into examples/public', function () {
    //             $this->assertEquals(
    //                 $this->tempDir['assets'],
    //                 $this->tempDir['examples']['public']['vendorname']['kitname']
    //             );
    //         });

    //         test('the file1.json has been merged successfull', function () {
    //             $expectedContent = [
    //                 'dependencies' => [
    //                     'dep1' => 'version1',
    //                     'dep2' => 'version2',
    //                     'dep3' => 'version3',
    //                     'dep4' => 'version4',
    //                     'dep5' => 'version5',
    //                 ],
    //                 'devDependencies' => [
    //                     'devDep1' => 'v1',
    //                     'devDep2' => 'v2',
    //                     'devDep3' => 'v3',
    //                     'devDep4' => 'v4',
    //                 ],
    //             ];

    //             $current = json_decode($this->tempDir['examples']['public']['file1.json'], true);

    //             $this->assertEquals($expectedContent, $current);
    //         });
    //     });

    //     tearDownAfterClass(function () {
    //         static::removeTempDir();
    //     });
    // });

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

            // testCase('exists an invalid composer.lock file', function () {
            //     setUp(function () {
            //         $this->composerLockFile = new vfsStreamFile('composer.lock');
            //         $this->composerLockFile->setContent(uniqid());

            //         $this->rootDir->addChild($this->composerLockFile);
            //     });

            //     foreach (THEN_COMMANDS as $command) {
            //         test("the command '{$command}' shows error message when the composer.lock file is invalid", function () use ($command) {
            //             $this->runCommand($command, []);

            //             $this->assertContains('The composer.lock file is corrupt.', $this->output);
            //         });
            //     }
            // });

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

                test('the command "list:packages" shows the expected kits', function () {
                    $this->runCommand('list:packages', []);

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

                // test('the command "kit:install" shows message The "then.json" file is missing.', function () {
                //     $this->runCommand('kit:install', []);

                //     $this->assertContains('The "then.json" file is missing.', $this->output);
                // });

                // testCase('exists an invalid then.json file', function () {
                //     setUp(function () {
                //         $this->thenJsonFile = new vfsStreamFile('then.json');
                //         $this->thenJsonFile->setContent(uniqid());

                //         $this->rootDir->addChild($this->thenJsonFile);
                //     });

                //     test('the command "kit:install" shows message The "then.json" file is corrupt.', function () {
                //         $this->runCommand('kit:install', []);

                //         $this->assertContains('The "then.json" file is corrupt.', $this->output);
                //     });
                // });
            });
        });
    });
});
