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
    if (is_object($value) && $config && $config->stringables) {
        $objectType = get_class($value);

        foreach ($config->stringables as $callback) {
            $reflector = new ReflectionFunction($callback);
            $arguments = $reflector->getParameters();

            if (count($arguments) === 0) {
                continue;
            }

            $argument = $arguments[0];

            if (!$argument->hasType()) {
                continue;
            }

            $argumentType = $argument->getType();

            $supportedTypes = [];

            if ($argumentType instanceof ReflectionNamedType) {
                $supportedTypes[] = $argumentType->getName();;
            } elseif ($argumentType instanceof ReflectionUnionType) {
                foreach ($argumentType->getTypes() as $type) {
                    if ($type instanceof ReflectionNamedType) {
                        $supportedTypes[] = $type->getName();
                    } elseif (class_exists('ReflectionIntersectionType') && $type instanceof ReflectionIntersectionType) {
                        throw new BladeException(Messages::ERROR_INTERSECTION_TYPES_NOT_SUPPORTED);
                    }
                }
            } elseif (class_exists('ReflectionIntersectionType') && $argumentType instanceof ReflectionIntersectionType) {
                throw new BladeException(Messages::ERROR_INTERSECTION_TYPES_NOT_SUPPORTED);
            }

            if (in_array($objectType, $supportedTypes) || array_reduce($supportedTypes, fn($carry, $item) => $carry || is_subclass_of($objectType, $item), false)) {
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
