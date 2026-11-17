<?php

namespace Tests\Support;

use Blade\ViewFinder;
use Override;

/**
 * Serves templates from memory, so a test can hand
 * Katana a template string the same way it hands
 * one to Laravel's Blade::render().
 */
class InMemoryViewFinder extends ViewFinder
{
    /**
     * @var array<string, string>
     */
    private array $templates = [];

    /**
     * Registers a template and returns the view name
     * it can be rendered with.
     */
    public function add(string $template): string
    {
        $name = hash('sha256', $template);

        $this->templates[$name] = $template;

        return $name;
    }

    #[Override]
    public function viewExists(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    #[Override]
    public function lastModified(string $name): int
    {
        return 0;
    }

    #[Override]
    public function getContents(string $name): string
    {
        return $this->templates[$name];
    }

    #[Override]
    public function identifier(): string
    {
        return self::class;
    }
}
