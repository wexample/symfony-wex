<?php

namespace Wexample\SymfonyWex\Interface;

use Wexample\SymfonyWex\Entity\App;

/**
 * A zone a bundle adds beside some files of an app where they are edited — a
 * document's PDF —, opened and closed from the bar of tools along the row,
 * as an IDE opens its tool windows.
 *
 * The board draws the icon and the zone; the zone loads the page at `url` the
 * first time it is opened, an embed.
 */
interface FileZoneInterface
{
    public const string TAG = 'wexample_symfony_wex.file_zone';

    /** The bar's group along the top, one zone open at a time in it. */
    public const string GROUP_START = 'start';

    /** The bar's group at its foot, one zone open at a time in it too. */
    public const string GROUP_END = 'end';

    /**
     * The zones this bundle has for the file, none when it has nothing to say
     * of it.
     *
     * @param string $path relative to the app
     *
     * @return list<array{name: string, label: string, icon: string, url: string, group: string}>
     *   `name` a key of its own — its open state is kept under it, for every
     *   file alike —, `label` already translated
     */
    public function zonesFor(App $app, string $path): array;
}
