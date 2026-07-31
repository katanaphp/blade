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
    public string $resolvedName;
    protected ViewFinder $viewFinder;

    public function __construct(public string $namespace, public string $name, public array $data, protected Blade $engine, public bool $componentDirectiveMode = false)
    {
        $this->attributes = new Attributes($data);

        if (strlen($this->namespace) !== 0) {
            $this->resolveFromNamespace();
            return;
        }

        $names = [
            "components.{$this->name}",
            "components.{$this->name}.{$this->name}",
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
                if ($viewFinder['finder']->viewExists($name)) {
                    $this->viewFinder = $viewFinder['finder'];
                    $this->resolvedName = $name;
                    $this->exists = true;

                    break;
                }
            }
        }
    }

    /**
     * Resolves a <x-namespace::component /> name against the view finder registered
     * for that namespace. A namespaced component is deliberately only looked up in
     * its own namespace, so it can neither shadow nor be shadowed by other components.
     */
    protected function resolveFromNamespace(): void
    {
        $namespace = $this->namespace;
        $component = $this->name;

        $viewFinder = $this->engine->config->getAnonymousComponentViewFinder($namespace);

        if (is_null($viewFinder) || $component === '') {
            return;
        }

        foreach ([$component, "{$component}.{$component}", "{$component}.index"] as $name) {
            if (!$viewFinder->viewExists($name)) {
                continue;
            }

            $this->viewFinder = $viewFinder;
            $this->resolvedName = $name;
            $this->exists = true;

            return;
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

    public function lastModified(): int
    {
        return $this->viewFinder->lastModified($this->resolvedName);
    }

    public function finderIdentifier(): string
    {
        return $this->viewFinder->identifier();
    }
}
