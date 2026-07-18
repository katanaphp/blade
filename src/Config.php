<?php

namespace Blade;

use Blade\Exceptions\BladeException;
use Closure;

class Config
{
    protected array $viewFinders = [];
    protected array $anonymousComponentViewFinders = [];

    public string $cachePath;

    protected array $directives = [];

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');

        $this->setAuth(fn() => throw new BladeException(Messages::ERROR_AUTH_CALLBACK_REQUIRED));
    }

    public function addViewPath(string $path): static
    {
        return $this->addViewFinder(new FileSystemViewFinder($path));
    }

    public function addViewFinder(ViewFinder $finder): static
    {
        $this->viewFinders[] = $finder;

        return $this;
    }

    /**
     * @return ViewFinder[]
     */
    public function getViewFinders(): array
    {
        return $this->viewFinders;
    }

    public function addAnonymousComponentViewFinder(ViewFinder $finder): static
    {
        $this->anonymousComponentViewFinders[] = $finder;

        return $this;
    }

    /**
     * @return ViewFinder[]
     */
    public function getAnonymousComponentViewFinders(): array
    {
        return $this->anonymousComponentViewFinders;
    }

    /**
     * @param Closure(...$params): bool $callback
     */
    public function setAuth(Closure $callback): static
    {
        $this->registerDirective('auth', $callback);
        $this->registerDirective('guest', fn(...$params) => !$callback(...$params));

        return $this;
    }

    public function registerDirective(string $name, Closure $callback): static
    {
        return $this->setDirective($name, $callback);
    }

    protected function setDirective(string $name, Closure $callback): static
    {
        $this->directives[$name] = $callback;

        return $this;
    }

    public function getDirective(string $name): ?callable
    {
        return $this->directives[$name] ?? null;
    }
}
