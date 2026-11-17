<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ForeachLoopTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testBasicForeach()
    {
        $this->assertEquals(
            $this->renderBlade('@foreach([1, 2, 3] as $item){{ "Hello" }}@endforeach'),
            "HelloHelloHello"
        );
    }

    public function testForeachWithKey()
    {
        $numbers = [1, 2, 3];

        $output = $this->renderBlade(
            '@foreach($numbers as $key => $value) {{ $key }} => {{ $value }} @endforeach',
            [
                'numbers' => $numbers,
            ]
        );

        foreach ($numbers as $key => $value) {
            $this->assertStringContainsString("$key => $value", $output);
        }
    }

    public function testForeachWithEmpty()
    {
        $this->assertEmpty(
            $this->renderBlade('@foreach([] as $item){{ "Hello" }}@endforeach')
        );
    }

    public function testForeachWithNestedParenthesis()
    {
        $this->assertEquals(
            $this->renderBlade('@foreach(range(0, 9) as $item){{ $item }}@endforeach'),
            "0123456789"
        );
    }
}
