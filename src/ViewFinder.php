<?php

namespace Blade;

abstract class ViewFinder
{
    /**
     * Determines whether a view exists.
     */
    abstract public function viewExists(string $name): bool;

    /**
     * Retrieves last modified time as UNIX timestamp
     * for a view.
     *
     * @return int UNIX timestamp
     */
    abstract public function lastModified(string $name): int;

    /**
     * Retrieves content of the blade template file
     * by name.
     */
    abstract public function getContents(string $name): string;

    /**
     * Retrieves a stable identifier that is unique to the
     * source a view name resolves to, such as a file path.
     */
    abstract public function identity(string $name): string;
}
