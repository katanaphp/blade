<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class StacksTest extends TestCase
{
    use VerifiesOutputTrait;

    public function test_stack(): void
    {
        $template = <<<'BLADE'
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
        $template = <<<'BLADE'
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
        $template = <<<'BLADE'
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
        $template = <<<'BLADE'
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

    public function test_hasstack_renders_content_when_stack_has_pushes(): void
    {
        $template = <<<'BLADE'
            @push('list')
                <li>Item</li>
            @endpush
            @hasstack('list')
                <ul>
                    @stack('list')
                </ul>
            @endif
        BLADE;

        $this->assertSame(
            '<ul> <li>Item</li> </ul>',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_renders_nothing_when_stack_has_no_pushes(): void
    {
        $template = <<<'BLADE'
            Before
            @hasstack('list')
                <ul>
                    @stack('list')
                </ul>
            @endif
            After
        BLADE;

        $this->assertSame(
            'Before After',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_with_else(): void
    {
        $template = <<<'BLADE'
            @hasstack('list')
                Has items
            @else
                No items
            @endif
        BLADE;

        $this->assertSame(
            'No items',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_only_checks_the_given_stack(): void
    {
        $template = <<<'BLADE'
            @push('scripts')
                <script src="https://cdn.example.com/script.js"></script>
            @endpush
            @hasstack('scripts')
                Has scripts
            @endif
            @hasstack('styles')
                Has styles
            @endif
        BLADE;

        $this->assertSame(
            'Has scripts',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_does_not_see_pushes_that_come_later(): void
    {
        $template = <<<'BLADE'
            @hasstack('list')
                Has items
            @else
                No items yet
            @endif
            @push('list')
                <li>Item</li>
            @endpush
        BLADE;

        $this->assertSame(
            'No items yet',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_in_layout_sees_pushes_of_extending_view(): void
    {
        $layout = <<<'BLADE'
            <main>@yield('content')</main>
            @hasstack('scripts')
                <footer>@stack('scripts')</footer>
            @endif
        BLADE;

        $template = <<<'BLADE'
            @extends('layout')
            @section('content', 'Page')
            @push('scripts')
                <script src="https://cdn.example.com/script.js"></script>
            @endpush
        BLADE;

        $this->createTemporaryBladeFile($layout, 'layout');

        $this->assertSame(
            '<main>Page</main> <footer> <script src="https://cdn.example.com/script.js"></script> </footer>',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function test_hasstack_in_layout_is_false_when_extending_view_pushes_nothing(): void
    {
        $layout = <<<'BLADE'
            <main>@yield('content')</main>
            @hasstack('scripts')
                <footer>@stack('scripts')</footer>
            @endif
        BLADE;

        $template = <<<'BLADE'
            @extends('layout')
            @section('content', 'Page')
        BLADE;

        $this->createTemporaryBladeFile($layout, 'layout');

        $this->assertSame(
            '<main>Page</main>',
            $this->removeIndentation($this->renderBlade($template))
        );
    }
}
