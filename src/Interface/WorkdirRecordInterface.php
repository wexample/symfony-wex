<?php

namespace Wexample\SymfonyWex\Interface;

/**
 * A row rebuilt from a record of `.wex/data`: what it holds is the file's, and
 * the file is where it says.
 */
interface WorkdirRecordInterface
{
    /** The record's file, null for a row no file stands behind. */
    public function getPath(): ?string;
}
