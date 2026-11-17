<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class WhileLoopTest extends TestCase
{
    use VerifiesOutputTrait;

    public function test_basic_while_loop()
    {
        $this->assertEquals(
            "0 1 2",
            preg_replace('/\s+/', ' ', trim($this->renderBlade(
                '@while($counter < 3) {{ $counter++ }} @endwhile',
                ['counter' => 0]
            ))),
        );
    }
}
