<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ForelseDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testWorksLikeForeach()
    {
        $this->assertSame(
            '0:1 1:2 2:3 ',
            $this->renderBlade(
                '@forelse([1, 2, 3] as $key => $item){{ $key }}:{{ $item }} @endforelse',
            )
        );
    }

    public function testRendersEmptyOutput()
    {
        $this->assertSame(
            'some empty content ',
            $this->renderBlade(
                '@forelse([] as $key => $item) not content @empty some empty content @endforelse',
            )
        );
    }
}
