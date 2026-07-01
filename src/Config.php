<?php

namespace Blade;

class Config
{
    protected array $viewFinders = [];

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
}
