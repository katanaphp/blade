<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class CommentsTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testComments(): void
    {
        $this->assertSame(
            "",
            $this->renderBlade("{{-- {{ 'Secret Message' }} --}}")
        );
    }

    public function testHasPresidenceOverComponents(): void
    {

        $this->createComponent(
            'alter',
            '<div>Hello World</div>'
        );

        $this->assertSame(
            "",
            $this->renderBlade("{{-- <x-alter/> --}}")
        );
        $this->assertSame(
            "",
            $this->renderBlade("{{-- <x-alter></x-alter> --}}")
        );
    }

    public function testHasPresidenceOverDirectives(): void
    {
        $this->assertSame(
            "",
            $this->renderBlade("{{-- @if(true) Hello @endif --}}")
        );
    }
}
