<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ForLoopTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testBasicForLoop(): void
    {
        $blade = '@for ($i = 0; $i < 10; $i++) {{ $i }} @endfor';

        $this->assertSame(
            '0 1 2 3 4 5 6 7 8 9',

            /**
             * Output contains white spaces from the indentation
             * and new lines. We are going to remove them to make
             * the comparison easier.
             */
            preg_replace(
                '/\s+/',
                ' ',
                trim($this->renderBlade($blade))
            )
        );
    }

    public function testEmptyParam(): void
    {
        $blade = <<<'BLADE'
        @for (;;)
            @if ($i > 9)
                @break
            @endif
            {{ $i }}
            @php $i++; @endphp
        @endfor
        BLADE;

        $this->assertSame(
            '0 1 2 3 4 5 6 7 8 9',

            /**
             * Output contains white spaces from the indentation
             * and new lines. We are going to remove them to make
             * the comparison easier.
             */
            preg_replace(
                '/\s+/',
                ' ',
                trim($this->renderBlade($blade, ['i' => 0]))
            )
        );
    }
}
