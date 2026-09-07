<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Yaml\Yaml;
use Wexample\PhpWex\Helper\WorkdirHelper;
use Wexample\SymfonyWex\Entity\App;

/**
 * Reads what an app says about itself in its own `.wex` directory.
 *
 * Where a manager holds a configuration beside the app, about the app, that is
 * a different source with a different owner: finding it is the caller's
 * business, and it is passed in rather than looked for here.
 */
class WorkdirReader
{
    public const CONFIG_FILE_NAME = 'config.yml';

    /**
     * @param string               $path  where the app is mounted
     * @param App|null             $into  the row already held for that path, overwritten in place
     * @param array<string, mixed> $about what someone else holds about that app
     *
     * @return App|null null when nothing is mounted there
     */
    public function read(
        string $path,
        ?App $into = null,
        array $about = [],
    ): ?App {
        if (! is_dir($path)) {
            return null;
        }

        // A directory carrying no `.wex` is still an app: a board mounts apps
        // that know nothing of wex, and they must appear rather than vanish.
        // What it says about itself wins over what is said about it.
        $config = array_replace_recursive($about, $this->config($path));
        $global = $config['global'] ?? [];

        $app = $into ?? new App($path);
        $app
            // The name wex knows it by, which is not always its directory: an
            // app installed in its own repository is read at the mount point of
            // the server, and `/var/www/html` names nothing.
            ->setName($global['name'] ?? basename($path))
            ->setDescription($global['description'] ?? null)
            ->setType($global['type'] ?? null)
            ->setVersion($global['version'] ?? null)
            // What wex says about itself, beside what the app says about itself.
            ->setWexVersion($config['wex']['version'] ?? null)
            ->setDomains($config['domains'] ?? []);

        return $app;
    }

    /**
     * @return array<string, mixed>
     */
    private function config(string $path): array
    {
        $file = WorkdirHelper::workdirPath($path).'/'.self::CONFIG_FILE_NAME;

        return is_file($file) ? Yaml::parseFile($file) ?? [] : [];
    }
}
