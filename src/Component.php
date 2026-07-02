<?php

namespace Blade;

use AllowDynamicProperties;

#[AllowDynamicProperties]
abstract class Component
{
    public Attributes $attributes;
    public array $props = [];
    public array $slots = [];

    protected bool $exists = false;
    protected string $resolvedName;
    protected ViewFinder $viewFinder;

    public function __construct(public string $name, public array $data, protected Blade $engine, public bool $componentDirectiveMode = false)
    {
        $this->attributes = new Attributes($data);

        $names = [
            "components.{$this->name}",
            "components.{$this->name}.index",
        ];

        if ($this->componentDirectiveMode) {
            array_unshift($names, $this->name);
        }

        foreach ($this->engine->config->getViewFinders() as $viewFinder) {
            if ($this->exists) {
                break;
            }

            foreach ($names as $name) {
                if ($viewFinder->viewExists($name)) {
                    $this->viewFinder = $viewFinder;
                    $this->resolvedName = $name;
                    $this->exists = true;

                    break;
                }
            }
        }

        foreach ($this->engine->config->getAnonymousComponentViewFinders() as $viewFinder) {
            if ($this->exists) {
                break;
            }

            $anonymousNames = [$this->name, $this->name . '.index'];

            foreach ($anonymousNames as $name) {
                if ($viewFinder->viewExists($name)) {
                    $this->viewFinder = $viewFinder;
                    $this->resolvedName = $name;
                    $this->exists = true;

                    break;
                }
            }
        }
    }

    public function viewExists(): bool
    {
        return $this->exists;
    }

    public function getContents(): string
    {
        return $this->viewFinder->getContents($this->resolvedName);
    }
}
