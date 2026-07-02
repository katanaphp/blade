<?php

namespace Blade;

class Config
{
    protected array $viewFinders = [];
    protected array $anonymousComponentViewFinders = [];

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
