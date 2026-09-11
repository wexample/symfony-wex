<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Yaml\Yaml;
use Wexample\PhpWex\Helper\WorkdirHelper;

/**
 * Reads what an app keeps under `.wex/data`, one file per record.
 *
 * A kind is a directory there — `agent`, `selection` — and what those records
 * mean is not known here: this hands over the values it found and the file they
 * came from, which is what tells one record from another.
 */
final readonly class WorkdirDataReader
{
    public const DIRECTORY = 'data';
    public const FILE_EXTENSION = 'yml';

    /**
     * Every record of that kind the app holds.
     *
     * @param string $path where the app is mounted
     * @param string $kind the directory the records sit in
     *
     * @return array<string, array<string, mixed>> the values of each record, by file
     */
    public function readAll(
        string $path,
        string $kind,
    ): array {
        $records = [];
        $pattern = $this->directory($path, $kind).'/*.'.self::FILE_EXTENSION;

        foreach (glob($pattern) ?: [] as $file) {
            $records[$file] = $this->read($file);
        }

        return $records;
    }

    /**
     * One record, named by the file the listing gave back.
     *
     * @return array<string, mixed>
     */
    public function read(string $file): array
    {
        return Yaml::parseFile($file) ?? [];
    }

    /**
     * Which app a record belongs to, read backwards off its own file.
     *
     * A record sits at `<app>/.wex/data/<kind>/<name>.yml`, and nothing inside
     * it says where it lives: the app is those four levels up. The inverse of
     * what `directory()` builds, and beside it for that reason.
     */
    public function appPath(string $file): string
    {
        return dirname($file, 4);
    }

    private function directory(
        string $path,
        string $kind,
    ): string {
        return WorkdirHelper::workdirPath($path).'/'.self::DIRECTORY.'/'.$kind;
    }
}
