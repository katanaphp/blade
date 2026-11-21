<?php

namespace Tests;

use Blade\View;
use PHPUnit\Framework\TestCase;

class FragmentDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testFragmentDirective()
    {
        $this->createTemporaryBladeFile(
            "Header
            @fragment('content')
                Content
            @endfragment
            Footer",
            'fragment',
        );

        $this->assertEquals(
            'Content',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragment('content')
            )
        );
    }

    public function testWithMultipleFragments()
    {
        $this->createTemporaryBladeFile(
            "Header
            @fragment('content')
                Content
            @endfragment
            @fragment('sidebar')
                Sidebar
            @endfragment
            Footer",
            'fragment',
        );

        $this->assertEquals(
            'Content',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragment('content')
            )
        );

        $this->assertEquals(
            'Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragment('sidebar')
            )
        );
    }

    public function testMultipleFragments(): void
    {
        $this->createTemporaryBladeFile(
            "Header
            @fragment('content')
                Content
            @endfragment
            @fragment('sidebar')
                Sidebar
            @endfragment
            Footer",
            'fragment',
        );

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments(['content', 'sidebar'])
            )
        );
    }

    public function testNestedFragments(): void
    {
        $this->createTemporaryBladeFile(
            "Header
            @fragment('content')
                Content
                @fragment('sidebar')
                    Sidebar
                @endfragment
            @endfragment
            Footer",
            'fragment',
        );

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragment('content')
            )
        );

        $this->assertEquals(
            'Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragment('sidebar')
            )
        );
    }

    public function testConditionalFragment(): void
    {
        $this->createTemporaryBladeFile(
            "Header
            @fragment('content')
                Content
            @endfragment
            Footer",
            'fragment',
        );

        $this->assertEquals(
            'Content',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentIf(true, 'content')
            )
        );

        $this->assertEquals(
            'Header Content Footer',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentIf(false, 'content')
            )
        );
    }

    public function testConditionalFragmentReturnsStringWhenConditionIsNotMet(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertIsString($this->blade->render('fragment')->fragmentIf(false, 'content'));
    }

    public function testFragmentsWithoutNamesReturnsAllFragments(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments()
            )
        );
    }

    public function testFragmentsWithoutNamesIsEmptyWhenViewHasNoFragments(): void
    {
        $this->createTemporaryBladeFile('Header Footer', 'fragment');

        $this->assertSame('', $this->blade->render('fragment')->fragments());
    }

    public function testFragmentsWithEmptyListReturnsNothing(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertSame('', $this->blade->render('fragment')->fragments([]));
    }

    public function testFragmentsWithoutNamesReturnsNestedFragmentsInTheOrderTheyClose(): void
    {
        $blade = <<<'BLADE'
            Header
            @fragment('content')
                Content
                @fragment('sidebar')
                    Sidebar
                @endfragment
            @endfragment
            Footer
        BLADE;

        $this->createTemporaryBladeFile($blade, 'fragment');

        $this->assertEquals(
            'Sidebar Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments()
            )
        );
    }

    public function testFragmentsWithoutNamesIncludesFragmentsOfIncludedViews(): void
    {
        $partial = <<<'BLADE'
            @fragment('partial')
                Partial
            @endfragment
        BLADE;

        $blade = <<<'BLADE'
            Header
            @fragment('content')
                Content
            @endfragment
            @include('partial')
            @fragment('sidebar')
                Sidebar
            @endfragment
            Footer
        BLADE;

        $this->createTemporaryBladeFile($partial, 'partial');
        $this->createTemporaryBladeFile($blade, 'fragment');

        $this->assertEquals(
            'Content Partial Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments()
            )
        );
    }

    public function testFragmentBeforeComponentIsKept(): void
    {
        $blade = <<<'BLADE'
            @fragment('content')
                Content
            @endfragment
            <x-box>Boxed</x-box>
            @fragment('sidebar')
                Sidebar
            @endfragment
        BLADE;

        $this->createComponent('box', '<div>{{ $slot }}</div>');
        $this->createTemporaryBladeFile($blade, 'fragment');

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments()
            )
        );
    }

    public function testFragmentsOfPreviousRenderAreNotKept(): void
    {
        $other = <<<'BLADE'
            @fragment('other')
                Other
            @endfragment
        BLADE;

        $this->createTemporaryBladeFile($other, 'other');
        $this->createViewWithTwoFragments();

        $this->blade->render('other')->render();

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragments()
            )
        );

        $this->assertSame('', $this->blade->render('fragment')->fragment('other'));
    }

    public function testConditionalFragmentsWhenConditionIsMet(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertEquals(
            'Sidebar Content',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(true, ['sidebar', 'content'])
            )
        );
    }

    public function testConditionalFragmentsWhenConditionIsNotMet(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertEquals(
            'Header Content Sidebar Footer',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(false, ['sidebar', 'content'])
            )
        );
    }

    public function testConditionalFragmentsWithoutNames(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertEquals(
            'Content Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(true)
            )
        );

        $this->assertEquals(
            'Header Content Sidebar Footer',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(false)
            )
        );
    }

    public function testConditionalFragmentsWithCallableCondition(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertEquals(
            'Sidebar',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(fn() => true, ['sidebar'])
            )
        );

        $this->assertEquals(
            'Header Content Sidebar Footer',
            $this->removeIndentation(
                $this->blade->render('fragment')->fragmentsIf(fn() => false, ['sidebar'])
            )
        );
    }

    public function testConditionalFragmentsReturnsStringWhenConditionIsNotMet(): void
    {
        $this->createViewWithTwoFragments();

        $this->assertIsString($this->blade->render('fragment')->fragmentsIf(false, ['content']));
    }

    private function createViewWithTwoFragments(): void
    {
        $blade = <<<'BLADE'
            Header
            @fragment('content')
                Content
            @endfragment
            @fragment('sidebar')
                Sidebar
            @endfragment
            Footer
        BLADE;

        $this->createTemporaryBladeFile($blade, 'fragment');
    }
}
