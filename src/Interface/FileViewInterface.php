<?php

namespace Wexample\SymfonyWex\Interface;

use Wexample\SymfonyWex\Entity\App;

/**
 * A page that shows some files of an app better than their bare detail does:
 * a document read as a document, with what it is and where it stands, rather
 * than as a path, a size and some text.
 *
 * The detail of a file asks every view in turn and hands the request over to
 * the first that claims the file, the address staying the one of the file. The
 * bare detail is still reached at its own address, for whoever wants the file
 * as it is.
 */
interface FileViewInterface
{
    public const string TAG = 'wexample_symfony_wex.file_view';

    /** The request attribute the claimed file's path is handed over in. */
    public const string ATTRIBUTE_PATH = 'path';

    /**
     * The controller drawing this file, as `Class::method`, or null to leave it
     * to the bare detail. It receives the app's `id` and the file's `path`
     * among the request attributes.
     *
     * @param string $path relative to the app
     */
    public function controllerFor(App $app, string $path): ?string;
}
