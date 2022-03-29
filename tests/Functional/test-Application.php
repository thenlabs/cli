<?php

namespace ThenLabs\Cli\Tests\Functional;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use ThenLabs\Cli\Application;
use ThenLabs\Cli\Tests\TestCase;

setTestCaseClass(TestCase::class);

testCase('test-Application.php', function () {
    staticProperty('application');

    setUpBeforeClass(function () {
        $filesystem = new Filesystem;

        if ($filesystem->exists(TEMP_PROJECT_DIR)) {
            $filesystem->remove(TEMP_PROJECT_DIR);
        }

        $filesystem->mirror(PROJECT_DIR, TEMP_PROJECT_DIR);

        $application = new Application;
        $application->setWorkingDirectory(TEMP_PROJECT_DIR);

        static::$application = $application;
    });

    testCase(function () {
        staticProperty('output');

        setUpBeforeClass(function () {
            $command = static::$application->find('assets:list');

            $commandTester = new CommandTester($command);
            $commandTester->execute([]);

            static::$output = $commandTester->getDisplay();
        });

        test(function () {
            $this->assertStringContainsString(
                '| vendor1/package1 | 2              | 4 B        |',
                static::$output
            );
        });

        test(function () {
            $this->assertStringContainsString(
                '| vendor1/package2 | 4              | 55 B       |',
                static::$output
            );
        });

        test(function () {
            $this->assertStringContainsString(
                '| vendor2/package2 | 5              | 15 B       |',
                static::$output
            );
        });

        testCase(function () {
            staticProperty('output');
            staticProperty('tempDir');

            setUpBeforeClass(function () {
                $command = static::$application->find('assets:copy');

                $commandTester = new CommandTester($command);
                $commandTester->execute([]);

                static::$output = $commandTester->getDisplay();
                static::$tempDir = static::readDirectoryInArray(TEMP_PROJECT_DIR);
            });

            test(function () {
                $this->assertEquals(
                    static::$tempDir['vendor']['vendor1']['package1']['assets'],
                    static::$tempDir['public']['vendor1']['package1'],
                );
            });

            test(function () {
                $this->assertEquals(
                    static::$tempDir['vendor']['vendor1']['package2']['resources'],
                    static::$tempDir['public']['vendor1']['package2'],
                );
            });

            test(function () {
                $this->assertEquals(
                    static::$tempDir['vendor']['vendor2']['package2']['assets'],
                    static::$tempDir['public']['vendor2']['package2'],
                );
            });
        });
    });
});
