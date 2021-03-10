<?php

namespace ThenLabs\Cli\Tests;

use Symfony\Component\Filesystem\Filesystem;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('ApplicationTest.php', function () {
    setUpBeforeClass(function () {
        $filesystem = new Filesystem;

        if ($filesystem->exists(TEMP_PROJECT_DIR)) {
            $filesystem->remove(TEMP_PROJECT_DIR);
        }

        $filesystem->mirror(PROJECT_DIR, TEMP_PROJECT_DIR);
    });

    test(function () {
        $this->assertTrue(true);
    });
});
