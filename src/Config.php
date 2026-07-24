<?php

namespace Blade;

use Blade\Exceptions\BladeException;
use Closure;

/**
 *
 * @psalm-type ComponentFinder = array{namespace: string, finder: ViewFinder}
 */
class Config
{
    protected array $viewFinders = [];

    /**
     * @var ComponentFinder[]
     */
    protected array $anonymousComponentViewFinders = [];

    public string $cachePath;

    protected array $directives = [];

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');

        $this->setAuthCallback(fn() => throw new BladeException(Messages::ERROR_AUTH_CALLBACK_REQUIRED));
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

    public function addAnonymousComponentViewFinder(ViewFinder $finder, string $namespace = ''): static
    {
        if ($namespace && $this->getAnonymousComponentNamespace($namespace)) {
            throw new BladeException(
                sprintf(
                    Messages::ERROR_MULTIPLE_PATH_FOR_NAMESPACE_NOT_ALLOWED,
                    $namespace
                ),
            );
        }

        $this->anonymousComponentViewFinders[] = [
            'namespace' => $namespace,
            'finder' => $finder,
        ];

        return $this;
    }

    /**
     * Register a path or view finder for anonymous components other than
     * the default view path.
     */
    public function addAnonymousComponentPath(string $path, string $namespace = ''): static
    {
        return $this->addAnonymousComponentViewFinder(new FileSystemViewFinder($path), $namespace);
    }

    /**
     * @return ComponentFinder[]
     */
    public function getAnonymousComponentViewFinders(): array
    {
        return $this->anonymousComponentViewFinders;
    }

    /**
     * @param Closure(...$params): bool $callback
     */
    public function setAuthCallback(Closure $callback): static
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

    public function getAnonymousComponentNamespace(string $namespace): ?ViewFinder
    {
        foreach ($this->anonymousComponentViewFinders as $finder) {
            if ($namespace === $finder['namespace']) {
                return $finder['finder'];
            }
        }

        return null;
    }
}
