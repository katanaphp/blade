<?php

namespace Blade;

use Blade\Interfaces\HtmlableInterface;
use ReflectionFunction;
use Stringable;

function e($value, ?Config $config = null): string
{
    if (is_object($value) && $config && $config->stringables) {
        $objectType = get_class($value);

        foreach ($config->stringables as $callback) {
            $reflector = new ReflectionFunction($callback);
            $arguments = $reflector->getParameters();

            if (count($arguments) === 0) {
                continue;
            }

            $argument = $arguments[0];

            if (
                $argument->hasType() &&
                (is_subclass_of($value, $argument->getType()->getName()) ||
                    $objectType === $argument->getType()->getName()
                )
            ) {
                $value = $callback($value);
                break;
            }
        }
    }

    if ($value === null) {
        return '';
    }

    if ($value instanceof HtmlableInterface) {
        return $value->toHtml();
    }

    if (is_scalar($value) || $value instanceof Stringable) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    return sprintf("Cannot convert value of type `%s` to string.", gettype($value));
}


function toKababCase(string $value): string
{
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
}


function toCamelCase(string $value): string
{
    return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
}
