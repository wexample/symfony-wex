<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\PhpWex\Helper\WorkdirHelper;

/**
 * Reads what an app keeps under `.wex/data`, one file per record.
 *
 * A kind is a directory there — `agent`, `selection` — and what those records
 * mean is not known here: this hands over the values it found and the file they
 * came from, which is what tells one record from another.
 *
 * A record is JSON: wex writes it, the board reads and writes it too, and the
 * browser ends up reading parts of it — one format for all three, where YAML
 * made the last one parse a text it had no reader for.
 */
final readonly class WorkdirDataReader
{
    public const DIRECTORY = 'data';
    public const FILE_EXTENSION = 'json';

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
        return self::decode((string) file_get_contents($file));
    }

    /**
     * Where the record of that kind bearing that identity sits.
     *
     * Written once because both ends compose it: whoever opens a record and
     * whoever is told to read one again have to land on the same file.
     */
    public function recordPath(
        string $path,
        string $kind,
        string $identity,
    ): string {
        return $this->directory($path, $kind).'/'.$identity.'.'.self::FILE_EXTENSION;
    }

    /**
     * Which app a record belongs to, read backwards off its own file.
     *
     * A record sits at `<app>/.wex/data/<kind>/<name>.json`, and nothing inside
     * it says where it lives: the app is those four levels up. The inverse of
     * what `directory()` builds, and beside it for that reason.
     */
    public function appPath(string $file): string
    {
        return dirname($file, 4);
    }

    /**
     * The values a record's text stands for.
     *
     * Beside the reader and public because the writer merges into what is
     * there, and both have to agree on what an empty file is: no values.
     *
     * @return array<string, mixed>
     */
    public static function decode(string $content): array
    {
        if ('' === trim($content)) {
            return [];
        }

        return json_decode($content, true, flags: JSON_THROW_ON_ERROR) ?? [];
    }

    /**
     * A record's values as the text it is written in.
     *
     * Four spaces, which is what PHP writes and what wex is asked to write:
     * a record rewritten by the other end must not come back reindented, or
     * every write reads as a change nobody made. Slashes and accents are left
     * as they are, since paths and French titles are most of what is in there.
     *
     * @param array<string, mixed> $values
     */
    public static function encode(array $values): string
    {
        return json_encode(
            $values,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        )."\n";
    }

    private function directory(
        string $path,
        string $kind,
    ): string {
        return WorkdirHelper::workdirPath($path).'/'.self::DIRECTORY.'/'.$kind;
    }
}
