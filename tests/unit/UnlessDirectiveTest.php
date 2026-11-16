<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class UnlessDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testWithTrue(): void
    {
        $this->assertSame(
            '',
            $this->renderBlade('@unless (true) False Here  @endunless')
        );
    }

    public function testWithFalse(): void
    {
        $this->assertSame(
            'You shouldn\'t be here',
            trim(
                $this->renderBlade('@unless (false) You shouldn\'t be here @endunless')
            )
        );
    }

    public function testWithElse(): void
    {
        $this->assertSame(
            'Truth leads here',
            trim(
                $this->renderBlade('@unless (true) You should be here @else Truth leads here @endunless')
            )
        );
    }

}
