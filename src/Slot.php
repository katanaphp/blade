<?php

namespace Blade;

use Attribute;
use Blade\Interfaces\HtmlableInterface;


/**
 * Represents a slot inside a component.
 */
class Slot implements HtmlableInterface
{
    public function __construct(
        private string $slot,
        public Attributes $attributes
    ) {
    }

    public function toHtml(): string
    {
        return $this->slot;
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Determine if the slot is empty.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return trim($this->slot) === '';
    }

    /**
     * Determine if the slot has actual content.
     *
     * @return boolean
     */
    public function hasActualContent(): bool
    {
        return trim(preg_replace('/<!--.*?-->/s', '', $this->slot)) !== '';
    }
}
