<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class StacksTest extends TestCase
{
    use VerifiesOutputTrait;

    public function test_stack(): void
    {
        $template = <<<BLADE
        Hello,
        @push('scripts')
            <script src="https://cdn.example.com/script.js"></script>
        @endpush
        World!
        @stack('scripts')
        BLADE;


        $expected = <<<HTML
        Hello,
        World!
        <script src="https://cdn.example.com/script.js"></script>
        HTML;

        $this->assertSame(
            $this->removeIndentation($expected),
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_multiple_stacks(): void
    {
        $template = <<<BLADE
        Hello,
        @push('scripts')
            <script src="https://cdn.example.com/script.js"></script>
        @endpush
        @push('css')
            <link rel="stylesheet" href="https://cdn.example.com/style.css">
        @endpush
        World!
        @push('scripts')
            <script src="https://cdn.example.com/another-script.js"></script>
        @endpush
        @stack('css')
        @stack('scripts')
        BLADE;

        $expected = <<<HTML
        Hello,
        World!
        <link rel="stylesheet" href="https://cdn.example.com/style.css">
        <script src="https://cdn.example.com/script.js"></script>
        <script src="https://cdn.example.com/another-script.js"></script>
        HTML;

        $this->assertSame(
            $this->removeIndentation($expected),
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_stack_with_no_pushes(): void
    {
        $template = <<<BLADE
        Hello,
        @stack('scripts')
        World!
        BLADE;

        $expected = <<<HTML
        Hello,
        World!
        HTML;

        $this->assertSame(
            $this->removeIndentation($expected),
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_nested_stack(): void
    {
        $template = <<<BLADE
        Hello,
        @push('scripts')
            <script src="https://cdn.example.com/script.js"></script>
            @push('scripts')
                <script src="https://cdn.example.com/another-script.js"></script>
            @endpush
        @endpush
        World!
        @stack('scripts')
        BLADE;

        $expected = <<<HTML
        Hello,
        World!
        <script src="https://cdn.example.com/another-script.js"></script>
        <script src="https://cdn.example.com/script.js"></script>
        HTML;

        $this->assertSame(
            $this->removeIndentation($expected),
            $this->removeIndentation($this->renderBlade($template))
        );
    }
}
