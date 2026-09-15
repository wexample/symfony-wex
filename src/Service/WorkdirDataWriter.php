<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Writes back a record of `.wex/data`, the counterpart of `WorkdirDataReader`.
 *
 * A record is addressed by its file, which is what the reader hands over along
 * with the values, so nothing here has to know the app it belongs to or the
 * kind it is. What a record means is not known here either: these are the
 * values it was given.
 */
final readonly class WorkdirDataWriter
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /**
     * Merges those values into the record and leaves the rest alone.
     *
     * A record holds more than whoever writes here owns — fields another writer
     * fills, fields nothing reads yet — so it is read, merged and dumped rather
     * than rebuilt.
     *
     * @param string               $file   the record, as the reader named it
     * @param array<string, mixed> $values the fields to put in it
     */
    public function write(
        string $file,
        array $values,
    ): void {
        $this->filesystem->dumpFile(
            $file,
            // Two spaces, like every `.wex` file wex writes itself: a rewrite
            // that reindents the whole file reads as a change nobody made.
            Yaml::dump(
                array_replace_recursive($this->parse($file), $values),
                4,
                2
            )
        );
    }

    /**
     * Opens the directory a record carries its files in.
     *
     * A record is one file, so whatever is bigger than a value it holds — a
     * transcript, the lock a turn takes while it runs — goes in a directory
     * beside it bearing the same identity. wex makes that directory the moment
     * it opens a record and writes in it afterwards without asking whether it
     * is there, so a record opened from here owes it the same.
     */
    public function openAssetsDirectory(string $file): void
    {
        $this->filesystem->mkdir($this->assetsDirectory($file));
    }

    /**
     * Takes a record away, and the directory it carried its files in.
     *
     * Both go, because both were opened here: a directory left behind would be
     * an identity still held by nothing, and the next record to take that same
     * uuid would inherit its contents.
     */
    public function remove(string $file): void
    {
        $this->filesystem->remove([
            $file,
            $this->assetsDirectory($file),
        ]);
    }

    private function assetsDirectory(string $file): string
    {
        return substr($file, 0, -(strlen(WorkdirDataReader::FILE_EXTENSION) + 1));
    }

    /**
     * @return array<string, mixed>
     */
    private function parse(string $file): array
    {
        return is_file($file) ? Yaml::parseFile($file) ?? [] : [];
    }
}
