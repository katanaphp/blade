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
}
