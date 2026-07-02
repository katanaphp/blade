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

    public function fragments(array $fragments): string
    {
        return $this->render(function (Blade $engine) use ($fragments) {
            $output = '';

            foreach ($fragments as $name) {
                $output .= $engine->getFragment($name);
            }

            return $output;
        });
    }

    public function fragmentIf(bool| callable $condition, string $name)
    {
        $value = is_callable($condition) ? $condition() : $condition;

        if ($value) {
            return $this->render(fn(Blade $engine) => $engine->getFragment($name));
        }

        return $this;
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
        $this->engine->renderContents($this->view, $this->data);
        $output =  ob_get_clean();

        if ($callback) {
            return $callback($this->engine);
        }

        return $output;
    }
}
