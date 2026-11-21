<?php

namespace Blade;

use Blade\Interfaces\HtmlableInterface;

class View implements HtmlableInterface
{
    public function __construct(protected Blade $engine, public string | Component $view, public array $data = []) {}

    public function with(array $data): static
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function fragment(string $name): string
    {
        return $this->render(fn(Blade $engine) => $engine->getFragment($name));
    }

    public function fragments(?array $fragments = null): string
    {
        return $this->render(function (Blade $engine) use ($fragments) {
            if ($fragments === null) {
                return implode('', $engine->getFragments());
            }

            $output = '';

            foreach ($fragments as $name) {
                $output .= $engine->getFragment($name);
            }

            return $output;
        });
    }

    public function fragmentIf(bool | callable $condition, string $name): string
    {
        if ($this->conditionIsMet($condition)) {
            return $this->fragment($name);
        }

        return $this->render();
    }

    /**
     * Renders the given fragments (or all of them) when
     * the condition is met, the whole view otherwise.
     */
    public function fragmentsIf(bool | callable $condition, ?array $fragments = null): string
    {
        if ($this->conditionIsMet($condition)) {
            return $this->fragments($fragments);
        }

        return $this->render();
    }

    private function conditionIsMet(bool | callable $condition): bool
    {
        if (is_callable($condition)) {
            return (bool) $condition();
        }

        return $condition;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function render(?callable $callback = null): string
    {
        ob_start();
        $this->engine->startRenderingView();

        try {
            $this->engine->renderContents($this->view, $this->data);
        } finally {
            $this->engine->stopRenderingView();
        }

        $output =  ob_get_clean();

        if ($callback) {
            return $callback($this->engine);
        }

        return $output;
    }
}
