<?php

namespace Blade;

use Closure;
use InvalidArgumentException;

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
    public function __construct(public string $name, public Closure $closure)
    {
        if (!preg_match("/^" . CompileAtRules::REGEX_DIRECTIVE_NAME . "$/i", $name)) {
            throw new InvalidArgumentException(sprintf(Messages::ERROR_INVALID_DIRECTIVE_NAME, $name));
        }
    }

    public function __invoke(...$args)
    {
        return call_user_func($this->closure, ...$args);
    }
}
