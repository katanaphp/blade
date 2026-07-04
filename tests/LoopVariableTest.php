<?php

namespace Tests;

use ArrayIterator;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Traversable;

class LoopVariableTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testForeachExposesLoopIndex()
    {
        $output = $this->renderBlade(
            '@foreach([1, 2, 3] as $item){{ $loop->index }}@endforeach'
        );

        $this->assertEquals($output, "012");
    }

    public function testForeachExposesLoopIteration()
    {
        $output = $this->renderBlade(
            '@foreach([1, 2, 3] as $item){{ $loop->iteration }}@endforeach'
        );

        $this->assertEquals($output, "123");
    }

    public function testForeachExposesLoopFirst()
    {
        $output = $this->renderBlade(
            '@foreach([1, 2, 3] as $item)' .
                '@if($loop->first)Only first!@endif' .
                '@endforeach'
        );

        $this->assertEquals(
            $output,
            "Only first!"
        );
    }

    public function testNestedLoopVariable(): void
    {
        $blade = <<<'BLADE'
            @foreach([1, 2, 3] as $item)
                {{ $loop->iteration }}
                @foreach([4] as $nestedItem)
                    {{ $loop->iteration }}
                @endforeach
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);
        $this->assertEquals(
            "1 1 2 1 3 1",
            $this->removeIndentation($output)
        );
    }

    public function testRemainingLoopVariables(): void
    {
        $blade = <<<'BLADE'
            @foreach([1, 2, 3] as $item)
                {{ $loop->remaining }}
            @endforeach
        BLADE;

        $output = $this->removeIndentation($this->renderBlade($blade));

        $this->assertEquals(
            "2 1 0",
            $output
        );
    }

    public function testCountVariable(): void
    {
        $numbers = range(1, 3);

        $blade = <<<'BLADE'
            @foreach($numbers as $item)
                {{ $loop->count }}
                @break;
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade, ['numbers' => $numbers]);

        $this->assertEquals(
            count($numbers),
            $this->removeIndentation($output)
        );
    }

    public function testLastVariable(): void
    {
        $numbers = range(1, 3);

        $blade = <<<'BLADE'
            @foreach([1, 2] as $item)
                @if($loop->last)
                    {{ $item }} is last
                @endif
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade, ['numbers' => $numbers]);

        $this->assertEquals(
            "2 is last",
            $this->removeIndentation($output)
        );
    }

    public function testLastVariableWithOneElement(): void
    {
        $blade = <<<'BLADE'
            @foreach([1] as $item)
                @if($loop->last)
                    {{ $item }} is last
                @endif
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);

        $this->assertEquals(
            "1 is last",
            $this->removeIndentation($output)
        );
    }

    public function testOddEvenVariables(): void
    {
        $blade = <<<'BLADE'
            @foreach([1, 2, 3] as $item)
                @if($loop->odd)
                    {{ $item }} is odd
                @endif
                @if($loop->even)
                    {{ $item }} is even
                @endif
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);

        $this->assertEquals(
            "1 is odd 2 is even 3 is odd",
            $this->removeIndentation($output)
        );
    }

    public function testDepthVariable(): void
    {
        $blade = <<<'BLADE'
            @foreach([1] as $lvl1)
                {{ $loop->depth }}
                @foreach([6] as $lvl2)
                    {{ $loop->depth }}
                    @foreach([7] as $lvl3)
                        {{ $loop->depth }}
                    @endforeach
                @endforeach
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);

        $this->assertEquals(
            "0 1 2",
            $this->removeIndentation($output)
        );
    }

    public function testParentProperty(): void
    {
        $blade = <<<'BLADE'
            @foreach([1] as $lvl1)
                @foreach([6] as $lvl2)
                        {{ $loop->parent->depth }}
                    @foreach([7] as $lvl3)
                        {{ $loop->parent->depth }}
                    @endforeach
                @endforeach
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);

        $this->assertEquals(
            "0 1",
            $this->removeIndentation($output)
        );
    }

    public function testLoopWithIterator()
    {
        $blade = <<<'BLADE'
            @foreach(new ArrayIterator([1, 2, 3]) as $key => $item)
                {{ $loop->index }}-{{ $key }}-{{ $item }}
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade);

        $this->assertEquals(
            "0-0-1 1-1-2 2-2-3",
            $this->removeIndentation($output)
        );
    }

    public function testLoopWithIteratorAggregate()
    {
        $data = new class([1, 2, 3]) implements IteratorAggregate {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function getIterator(): Traversable
            {
                return new ArrayIterator($this->data);
            }
        };

        $blade = <<<'BLADE'
            @foreach($numbers as $key => $item)
                {{ $loop->index }}-{{ $key }}-{{ $item }}
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade, ['numbers' => $data]);

        $this->assertEquals(
            "0-0-1 1-1-2 2-2-3",
            $this->removeIndentation($output)
        );
    }

    public function testLoopWithGenerator()
    {
        $data = function () {
            yield 1;
            yield 2;
            yield 3;
        };

        $blade = <<<'BLADE'
            @foreach($numbers() as $key => $item)
                {{ $loop->index }}-{{ $key }}-{{ $item }}
            @endforeach
        BLADE;

        $output = $this->renderBlade($blade, ['numbers' => $data]);

        $this->assertEquals(
            "0-0-1 1-1-2 2-2-3",
            $this->removeIndentation($output)
        );
    }
}
