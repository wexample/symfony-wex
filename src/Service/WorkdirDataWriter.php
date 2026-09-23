<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Filesystem\Filesystem;

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
     * Merged on the first level only, as wex merges: a field given here is the
     * whole field. A nested value merged key by key would keep an option the
     * form just removed.
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
            WorkdirDataReader::encode(
                array_replace($this->parse($file), $values)
            )
        );

        // A run record is written from both ends — the board web opens it as
        // root, a worker fills it in as its own user — over a bind mount they
        // share no uid on. Left at the creator's default, the second writer
        // cannot touch what the first made; world-writable, either one can.
        $this->filesystem->chmod($file, 0666);
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
        return is_file($file)
            ? WorkdirDataReader::decode((string) file_get_contents($file))
            : [];
    }
}
