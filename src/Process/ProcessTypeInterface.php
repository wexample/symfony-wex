<?php

namespace Wexample\SymfonyWex\Process;

/**
 * A treatment a process can name.
 *
 * Declared and not listed: a bundle mounted in the board brings the code doing
 * the work, and implementing this is what puts its name in the choices a
 * process is written with. Nothing central holds the vocabulary.
 *
 * What running one means is not here yet — a run is its own record, and this
 * says which treatments exist, not how they are carried out.
 */
interface ProcessTypeInterface
{
    public const string TAG = 'wexample_symfony_wex.process_type';

    /**
     * The name the record keeps, and the only thing tying a declaration to this
     * code: it survives a rename of the label and must not change.
     */
    public function getName(): string;

    /** What a human choosing it reads. */
    public function getLabel(): string;
}
