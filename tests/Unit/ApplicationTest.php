<?php

namespace ThenLabs\Cli\Tests\Unit;

use ThenLabs\Cli\Application;
use ThenLabs\Cli\Tests\TestCase;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('ApplicationTest.php', function () {
    test(function () {
        $application = new Application;
        $directory = uniqid('dir');

        $application->setWorkingDirectory($directory);

        $this->assertSame($directory, $application->getWorkingDirectory());
    });
});
