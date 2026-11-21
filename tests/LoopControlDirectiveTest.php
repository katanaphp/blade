<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class LoopControlDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testBreakWithoutCondition(): void
    {
        $this->assertSame(
            '1',
            $this->renderBlade('@foreach([1, 2, 3] as $item){{ $item }}@break @endforeach')
        );
    }

    public function testContinueWithoutCondition(): void
    {
        $this->assertSame(
            '123',
            $this->renderBlade('@foreach([1, 2, 3] as $item){{ $item }}@continue skipped @endforeach')
        );
    }

    public function testBreakWithCondition(): void
    {
        $this->assertSame(
            '12',
            $this->renderBlade('@foreach([1, 2, 3, 4] as $item)@break($item === 3){{ $item }}@endforeach')
        );
    }

    public function testContinueWithCondition(): void
    {
        $this->assertSame(
            '134',
            $this->renderBlade('@foreach([1, 2, 3, 4] as $item)@continue($item === 2){{ $item }}@endforeach')
        );
    }

    public function testConditionThatIsNeverMetHasNoEffect(): void
    {
        $this->assertSame(
            '123123',
            $this->renderBlade(
                '@foreach([1, 2, 3] as $item)@break($item === 9){{ $item }}@endforeach' .
                    '@foreach([1, 2, 3] as $item)@continue($item === 9){{ $item }}@endforeach'
            )
        );
    }

    public function testConditionWithSpaceBeforeParenthesis(): void
    {
        $this->assertSame(
            '12',
            $this->renderBlade('@foreach([1, 2, 3, 4] as $item)@break ($item === 3){{ $item }}@endforeach')
        );
    }

    public function testConditionWithNestedParenthesis(): void
    {
        $this->assertSame(
            '14',
            $this->renderBlade('@foreach([1, 2, 3, 4] as $item)@continue(in_array($item, [2, (3)])){{ $item }}@endforeach')
        );
    }

    public function testConditionUsingLoopVariable(): void
    {
        $this->assertSame(
            '23',
            $this->renderBlade('@foreach([1, 2, 3] as $item)@continue($loop->first){{ $item }}@endforeach')
        );
    }

    public function testConditionUsingViewData(): void
    {
        $this->assertSame(
            '12',
            $this->renderBlade(
                '@foreach($items as $item)@break($item > $limit){{ $item }}@endforeach',
                ['items' => [1, 2, 3, 4], 'limit' => 2]
            )
        );
    }

    public function testBreakWithConditionInForLoop(): void
    {
        $this->assertSame(
            '012',
            $this->renderBlade('@for($i = 0; $i < 10; $i++)@break($i === 3){{ $i }}@endfor')
        );
    }

    public function testContinueWithConditionInForLoop(): void
    {
        $this->assertSame(
            '0134',
            $this->renderBlade('@for($i = 0; $i < 5; $i++)@continue($i === 2){{ $i }}@endfor')
        );
    }

    public function testBreakWithConditionInWhileLoop(): void
    {
        $this->assertSame(
            '012',
            $this->renderBlade('@php $i = 0; @endphp@while(true)@break($i === 3){{ $i++ }}@endwhile')
        );
    }

    public function testBreakWithConditionInForelseLoop(): void
    {
        $this->assertSame(
            '12',
            $this->renderBlade('@forelse([1, 2, 3] as $item)@break($item === 3){{ $item }}@empty none @endforelse')
        );
    }

    public function testContinueWithConditionOnlyAffectsInnerLoop(): void
    {
        $this->assertSame(
            'a1a3b1b3',
            $this->renderBlade(
                '@foreach(["a", "b"] as $outer)@foreach([1, 2, 3] as $inner)' .
                    '@continue($inner === 2){{ $outer }}{{ $inner }}' .
                    '@endforeach@endforeach'
            )
        );
    }
}
