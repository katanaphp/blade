<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class OnceDirectiveTest extends TestCase
{

    use VerifiesOutputTrait;

    public function testOnceDirective()
    {
        $output = $this->renderBlade('
            @foreach([1, 2, 3] as $item)
                @once
                    <div>Once</div>
                @endonce
            @endforeach
        ');


        $this->assertSame(
            '<div>Once</div>',
            $this->removeIndentation($output)
        );
    }

    public function testSameOnceTwiceDirective()
    {
        $output = $this->renderBlade('
            @foreach([1, 2, 3] as $item)
                @once
                    <div>Once</div>
                @endonce
                @once
                    <div>Once</div>
                @endonce
            @endforeach
        ');


        $this->assertSame(
            '<div>Once</div> <div>Once</div>',
            $this->removeIndentation($output)
        );
    }
}
