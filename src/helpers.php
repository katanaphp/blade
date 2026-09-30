<?php

namespace Blade;

use Blade\Exceptions\BladeException;
use Blade\Interfaces\HtmlableInterface;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Stringable;

function e($value, ?Config $config = null): string
{
    if ($value === null) {
        return '';
    }

    if ($value instanceof HtmlableInterface) {
        return $value->toHtml();
    }

    if (is_scalar($value) || $value instanceof Stringable) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    return sprintf(Messages::ERROR_CANNOT_CAST_TO_STRING, gettype($value));
}


function toKababCase(string $value): string
{
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
}


function toCamelCase(string $value): string
{
    return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
}
