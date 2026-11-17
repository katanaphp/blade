<?php

namespace Blade;

class Loop
{
    public bool $first = true;

    /**
     * Indicates if the current element is the
     * last element in the iteration.
     *
     * Remains null if the loop is not countable.
     */
    public ?bool $last = null;

    public int $index = -1;
    public int $iteration = 0;

    /**
     * In case of a countable loop, these properties
     * will be set. Otherwise they remain null.
     */
    public ?int $count = null;
    public ?int $remaining = null;

    public bool $even;
    public bool $odd;

    public ?self $parent = null;

    // The nesting level of the loop, starting at 1.
    public int $depth = 1;

    public function setData($data, ?self $parent = null)
    {
        $this->parent = $parent;

        if ($this->parent) {
            $this->depth = $this->parent->depth + 1;
        }

        if (is_countable($data)) {
            $this->count = count($data);
            $this->remaining = $this->count - $this->iteration;
        }

        return $data;
    }

    public function increment(): void
    {
        $this->index++;
        $this->iteration++;

        $this->first = $this->iteration === 1;
        $this->even = $this->iteration % 2 === 0;
        $this->odd = !$this->even;

        if (isset($this->count)) {
            $this->remaining = $this->count - $this->iteration;
            $this->last = $this->iteration === $this->count;
        }
    }
}
