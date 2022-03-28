<?php
declare(strict_types=1);

namespace App\Tests;


trait MockDataTrait
{
    /**
     * @param string $dirName
     * @param string $name
     * @return bool|array
     */
    private static function getMockData(string $dirName, string $name)
    {
        $contents = @file_get_contents(sprintf('%s/%s/%s.%s', __DIR__ . '/Mock', $dirName, $name, 'json'));

        if (false === $contents) {
            return null;
        }

        $contents = preg_replace('/\\r\\n?/', "\n", $contents);

        return is_null($contents) ? false : json_decode($contents, true);
    }
}