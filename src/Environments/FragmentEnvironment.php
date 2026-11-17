<?php


namespace Blade\Environments;

use Exception;

trait FragmentEnvironment
{
    public array $fragments = [];
    public array $fragmentStack = [];

    /**
     * Number of views that are currently being rendered. Includes,
     * layouts and components render views inside other views.
     */
    protected int $viewsBeingRendered = 0;

    /**
     * Marks the start of a view render. Fragments belong to a single
     * render, so the ones left over from a previous render are dropped
     * when a new outermost view starts.
     */
    public function startRenderingView(): void
    {
        if ($this->viewsBeingRendered === 0) {
            $this->fragments = [];
            $this->fragmentStack = [];
        }

        $this->viewsBeingRendered++;
    }

    public function stopRenderingView(): void
    {
        $this->viewsBeingRendered--;
    }

    public function startFragment(string $name): void
    {
        ob_start();

        $this->fragmentStack[] = $name;
    }

    public function endFragment(): void
    {
        if (empty($this->fragmentStack)) {
            throw new Exception('Cannot end a fragment without starting one.');
        }

        $fragment = array_pop($this->fragmentStack);

        echo $this->fragments[$fragment] = ob_get_clean();
    }

    public function getFragment(string $name): string
    {
        return $this->fragments[$name] ?? '';
    }

    /**
     * All fragments of the current render, in the order they were closed.
     *
     * @return array<string, string>
     */
    public function getFragments(): array
    {
        return $this->fragments;
    }
}
