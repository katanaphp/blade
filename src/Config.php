<?php

namespace Blade;

class Config
{
    protected array $viewFinders = [];
    protected array $anonymousComponentViewFinders = [];

    public string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
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
}
