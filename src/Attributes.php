<?php

namespace Blade;

use Blade\Interfaces\HtmlableInterface;
use IteratorAggregate;
use Stringable;
use Traversable;

class Attributes implements HtmlableInterface, IteratorAggregate
{
    final public function __construct(protected array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function class(string | array $defaultClasses): static
    {
        return new static(
            array_merge(
                $this->attributes,
                ['class' => $this->mergeClass($defaultClasses)]
            )
        );
    }

    private function mergeClass(string | array $defaultClasses): string
    {
        $defaultClasses =  is_string($defaultClasses) ? explode(" ", $defaultClasses) : $defaultClasses;

        $classes = $this->attributes['class'] ?? '';

        $classes = sprintf(
            "%s %s",
            $classes,
            implode(" ", Blade::filterConditionalValues($defaultClasses))
        );

        $classes = trim($classes);

        return $classes;
    }

    public function merge(array $defaultAttributes): static
    {
        $attributes = $this->attributes;

        foreach ($defaultAttributes as $key => $value) {

            if (!isset($attributes[$key])) {
                $attributes[$key] = '';
            }

            if ($key === 'class') {
                $attributes['class'] = $this->mergeClass($value);
                continue;
            }

            if ($value instanceof AppendableAttributeValue) {
                $attributes[$key] = $value . $attributes[$key];
                continue;
            }

            $attributes[$key] .= $value;
        }

        return new static($attributes);
    }

    public function removeProps(array $props): static
    {
        return $this->filter(
            fn(string $name): bool => !in_array($name, $props) && !in_array(toCamelCase($name), $props)
        );
    }

    /**
     * Prevents attributes from being rendered.
     *
     * @return static
     */
    public function except(string|array $attributes): static
    {
        $attributes = is_string($attributes) ? [$attributes] : $attributes;

        return $this->filter(fn(string $key) => !in_array($key, $attributes));
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    public function toHtml(): string
    {
        $output = [];

        foreach ($this->attributes as $key => $value) {
            if (!is_scalar($value) && !($value instanceof Stringable)) {
                continue;
            }

            $value = htmlentities($value, ENT_QUOTES, 'UTF-8', false);

            $output[] = "{$key}='{$value}'";
        }

        return implode(" ", $output);
    }


    public function get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->attributes);
    }

    /**
     * Determine if one or more attributes are set.
     *
     * @param  string|array $keys
     * @return bool
     */
    public function has(string|array $keys): bool
    {
        $found = true;

        $keys = is_string($keys) ? [$keys] : $keys;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->attributes)) {
                $found = false;
                break;
            }
        }

        return $found;
    }

    /**
     * Determine if any of the given attributes are present.
     *
     * @param array $keys
     * @return boolean
     */
    public function hasAny(array $keys): bool
    {
        $found = false;

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->attributes)) {
                $found = true;
                break;
            }
        }

        return $found;
    }


    public function filter(callable $callback): static
    {
        $attributes = array_filter(
            $this->attributes,
            fn($value, string $key) => $callback($key, $value),
            ARRAY_FILTER_USE_BOTH
        );

        return new static($attributes);
    }

    public function whereStartsWith(string $needle): static
    {
        return $this->filter(fn(string $key) => str_starts_with($key, $needle));
    }

    public function whereDoesntStartWith(string $needle): static
    {
        return $this->filter(fn(string $key) => !str_starts_with($key, $needle));
    }

    public function first(): static
    {
        return new static(array_slice($this->attributes, 0, 1, true));
    }

    /**
     * Prepends a value to the attribute.
     *
     * @param string $value
     * @return AppendableAttributeValue
     */
    public function prepends(string $value): AppendableAttributeValue
    {
        return new AppendableAttributeValue($value);
    }
}
