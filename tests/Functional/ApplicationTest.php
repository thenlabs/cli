<?php

namespace ThenLabs\Cli\Tests\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use ThenLabs\Cli\Application;
use ThenLabs\Cli\Tests\TestCase;

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
            $this->assertContains(
                '| vendor1/package1 | 2              | 4 B        |',
                $this->output
            );
        });

        test(function () {
            $this->assertContains(
                '| vendor1/package2 | 4              | 55 B       |',
                $this->output
            );
        });

        test(function () {
            $this->assertContains(
                '| vendor2/package2 | 5              | 15 B       |',
                $this->output
            );
        });

        testCase(function () {
            setUpBeforeClass(function () {
                $application = static::getVar('application');
                $command = $application->find('assets:copy');

                $commandTester = new CommandTester($command);
                $commandTester->execute([]);

                $output = $commandTester->getDisplay();

                static::setVar('output', $output);
                static::setVar('tempDir', static::readDirectoryInArray(TEMP_PROJECT_DIR));
            });

            test(function () {
                $this->assertEquals(
                    $this->tempDir['vendor']['vendor1']['package1']['assets'],
                    $this->tempDir['public']['vendor1']['package1'],
                );
            });

            test(function () {
                $this->assertEquals(
                    $this->tempDir['vendor']['vendor1']['package2']['resources'],
                    $this->tempDir['public']['vendor1']['package2'],
                );
            });

            test(function () {
                $this->assertEquals(
                    $this->tempDir['vendor']['vendor2']['package2']['assets'],
                    $this->tempDir['public']['vendor2']['package2'],
                );
            });
        });
    });
});
