<?php

namespace Blade;

use Closure;

class Directive
{
    /**
     * Determines if the closure should be
     * used for generating PHP at compile time, or
     * called at runtime.
     *
     * @var bool
     */
    public bool $isConditional = false;
    public function __construct(public string $name, public Closure $closure) {}

    public function __invoke(...$args)
    {
        return call_user_func($this->closure, ...$args);
    }
}
