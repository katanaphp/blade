<?php

namespace Blade\Compilers;

trait StackCompiler
{
    public function compilePush(string $expression): string
    {
        return "<?php \$__env->startPush{$expression}; ?>";
    }

    public function compileEndPush(string $expression): string
    {
        return "<?php \$__env->endPush(); ?>";
    }

    public function compileStack(string $expression): string
    {
        return "<?php echo \$__env->getStack{$expression}; ?>";
    }

    /**
     * Opens an if block that is closed with @endif and
     * entered when something was pushed to the stack.
     */
    public function compileHasStack(string $expression): string
    {
        return "<?php if(\$__env->hasStack{$expression}): ?>";
    }
}
