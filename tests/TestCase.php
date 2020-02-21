<?php
declare(strict_types=1);

namespace ThenLabs\Cli\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Andaniel05\PyramidalTests\Utils\StaticVarsInjectionTrait;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestCase extends BaseTestCase
{
    use StaticVarsInjectionTrait;

    public static function readDirectoryInArray(string $directory): array
    {
        $result = [];

        $readDir = function (string $directory, array &$result) use (&$readDir) {
            foreach (scandir($directory) as $filename) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }

                $fullFilename = $directory.'/'.$filename;

                if (is_dir($fullFilename)) {
                    $result[$filename] = [];
                    $readDir($fullFilename, $result[$filename]);
                } else {
                    $result[$filename] = file_get_contents($fullFilename);
                }
            }
        };

        $readDir($directory, $result);

        return $result;
    }
}
