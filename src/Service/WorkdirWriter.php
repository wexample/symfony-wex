<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Wexample\PhpWex\Helper\WorkdirHelper;

/**
 * Writes back into the `.wex` directory what was read from it.
 *
 * The counterpart of the reader, and the half that makes the file the owner: a
 * value edited elsewhere is not kept elsewhere, it is put back where the next
 * read will find it.
 */
class WorkdirWriter
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * Merges those values into the configuration and leaves the rest alone.
     *
     * The file is hand-written more often than not, so it is read, merged and
     * dumped rather than rebuilt: what nobody edited here survives the write.
     *
     * @param string               $path   where the app is mounted
     * @param array<string, mixed> $values nested as the configuration is
     */
    public function write(
        string $path,
        array $values,
    ): void {
        $file = WorkdirHelper::workdirPath($path).'/'.WorkdirReader::CONFIG_FILE_NAME;

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
     * @return array<string, mixed>
     */
    private function parse(string $file): array
    {
        return is_file($file) ? Yaml::parseFile($file) ?? [] : [];
    }
}
