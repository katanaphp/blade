<?php

namespace Blade\Environments;

trait StackEnvironment
{
    protected array $stacks = [];
    protected array $stackLog = [];

    public function startPush(string $name): void
    {
        ob_start();

        $this->stackLog[] = $name;
    }

    public function endPush(): void
    {
        $stack = array_pop($this->stackLog);

        $this->stacks[$stack][] = ob_get_clean();
    }

    public function getStack(string $name): string
    {
        return implode('', $this->stacks[$name] ?? []);
    }

    public function hasStack(string $name): bool
    {
        return isset($this->stacks[$name]);
    }
}
