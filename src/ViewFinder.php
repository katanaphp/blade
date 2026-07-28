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
     * Retrieves a stable identifier that is unique to finder
     * such as vendor/package, or file path in case of
     * file system view finder.
     */
    abstract public function identifier(): string;
}
