<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class VerbatimDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testDirective(): void
    {
        $content = <<<CONTENT
        {{-- Content inside this block --}}
        {{-- Should be rendered as is  --}}
        CONTENT;

        $blade = <<<BLADE
        @verbatim
        $content
        @endverbatim
        BLADE;

        $this->assertSame(
            $content,
            trim($this->renderBlade($blade))
        );
    }

    public function testMultipleVerbatim(): void
    {
        $content = '@if($content) {{ $content }} @endif';

        $blade = "@verbatim $content @endverbatim" .
            "@verbatim $content @endverbatim";

        $this->assertSame(
            $content . " " .  $content,
            preg_replace('/\s+/', ' ', trim($this->renderBlade($blade)))
        );
    }
}
