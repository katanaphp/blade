<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class IncludeDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testIncludeDirective()
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. This is sub view content',
            $this->renderBlade('This is the main view. @include("subview")')
        );
    }

    public function testIncludeDirectiveWithVariables()
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade('This is the main view. @include("subview", ["name" => "John"])')
        );
    }


    public function testIncludeDirectiveInheritsVariables()
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade(
                'This is the main view. @include("subview")',
                ['name' => 'John']
            )
        );
    }

    public function testIncludeIf(): void
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. This is sub view content',
            $this->renderBlade('This is the main view. @includeIf("subview")')
        );
    }

    public function testIncludeIfWithVariables(): void
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade('This is the main view. @includeIf("subview", ["name" => "John"])')
        );
    }

    public function testIncludeIfInheritsVariables(): void
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade(
                'This is the main view. @includeIf("subview")',
                ['name' => 'John']
            )
        );
    }

    public function testIncludeIfDoesntThrowErrorWhenViewNotFound(): void
    {
        $this->assertSame(
            'This is the main view. ',
            $this->renderBlade('This is the main view. @includeIf("non-existing-subview")')
        );
    }

    public function testIncludeWhenTrue(): void
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. This is sub view content',
            $this->renderBlade('This is the main view. @includeWhen(true, "subview")')
        );
    }

    public function testIncludeWhenFalse(): void
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. ',
            $this->renderBlade('This is the main view. @includeWhen(false, "subview")')
        );
    }

    public function testIncludeWhenWithVariables(): void
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade('This is the main view. @includeWhen(true, "subview", ["name" => "John"])')
        );
    }

    public function testIncludeWhenInheritsVariables(): void
    {
        $this->createTemporaryBladeFile('Hello, {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello, John',
            $this->renderBlade(
                'This is the main view. @includeWhen(true, "subview")',
                ['name' => 'John']
            )
        );
    }


    public function testIncludeUnlessTrue(): void
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. ',
            $this->renderBlade('This is the main view. @includeUnless(true, "subview")')
        );
    }

    public function testIncludeUnlessFalse(): void
    {
        $this->createTemporaryBladeFile('This is sub view content', 'subview');

        $this->assertSame(
            'This is the main view. This is sub view content',
            $this->renderBlade('This is the main view. @includeUnless(false, "subview")')
        );
    }

    public function testIncludeUnlessWithVariables(): void
    {
        $this->createTemporaryBladeFile('Hello {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello John',
            $this->renderBlade('This is the main view. @includeUnless(false, "subview", ["name" => "John"])')
        );
    }

    public function testIncludeUnlessInheritsVariables(): void
    {
        $this->createTemporaryBladeFile('Hello {{ $name }}', 'subview');

        $this->assertSame(
            'This is the main view. Hello John',
            $this->renderBlade(
                'This is the main view. @includeUnless(false, "subview")',
                ['name' => 'John']
            )
        );
    }

    public function testIncludeFirst(): void
    {
        $this->createTemporaryBladeFile(
            'This is subview 2.',
            'view-2',
        );

        $this->createTemporaryBladeFile(
            'This is subview 3.',
            'view-3',
        );


        $this->assertSame(
            'This is main view. This is subview 2.',
            $this->renderBlade(
                'This is main view. @includeFirst(["view-1", "view-2", "view-3"])'
            )
        );
    }

    public function testIncludeFirstWithEmpty(): void
    {
        $this->assertSame(
            'This is main view. ',
            $this->renderBlade(
                'This is main view. @includeFirst([])'
            )
        );
    }

    public function testIncludeFirstWithVariables(): void
    {
        $this->createTemporaryBladeFile(
            'Hello, {{ $name }}',
            'existing-subview',
        );

        $this->assertSame(
            'This is main view. Hello, John',
            $this->renderBlade(
                'This is main view. @includeFirst(["view-1", "view-2", "existing-subview"], ["name" => "John"])'
            )
        );
    }

    public function testIncludeFirstInheritsVariables(): void
    {
        $this->createTemporaryBladeFile(
            'Hello, {{ $name }}',
            'existing-subview',
        );

        $this->assertSame(
            'This is main view. Hello, John',
            $this->renderBlade(
                'This is main view. @includeFirst(["view-1", "view-2", "existing-subview"])',
                ['name' => 'John']
            )
        );
    }

    public function testIsolatedIncludesView(): void
    {
        $template = "Include isolated view";

        $this->createTemporaryBladeFile(
            $template,
            'include-isolated'
        );

        $this->assertSame(
            $template,
            $this->renderBlade('@includeIsolated("include-isolated")')
        );
    }

    public function testIsolatedDoesNotReceiveContextFromParent(): void
    {
        $this->createTemporaryBladeFile(
            'Hello, {{ $name ?? "Kitty" }}',
            'include-isolated',
        );

        $this->assertSame(
            'Hello, Kitty',
            $this->renderBlade(
                "@includeIsolated('include-isolated')",
                ["name" => "Jhon"]
            )
        );
    }

    public function testIsolatedReceivesPassedData(): void
    {
        $this->createTemporaryBladeFile(
            "Hello, {{ \$name }}",
            "include-isolated",
        );

        $this->assertSame(
            'Hello, Maria',
            $this->renderBlade("@includeIsolated('include-isolated', ['name' => 'Maria'])")
        );
    }
}
