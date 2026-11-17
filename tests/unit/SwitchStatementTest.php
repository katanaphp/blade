<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SwitchStatementTest extends TestCase
{
    use VerifiesOutputTrait;

    public function test_basic()
    {
        $blade = <<<'BLADE'
        @switch($i)
            @case(1)
                First case...
                @break
            @default
            Default case...
        @endswitch
        BLADE;

        $cases = [
            0 => 'Default case...',
            1 => 'First case...',
        ];

        foreach ($cases as $key => $case) {
            $this->assertSame(
                $case,
                trim($this->renderBlade($blade, ['i' => $key]))
            );
        }
    }

    public function test_default_case()
    {
        $blade = <<<'BLADE'
        @switch($i)
            @default
                Default case...
        @endswitch
        BLADE;

        $cases = [
            0 => 'Default case...',
            1 => 'First case...',
            2 => 'Second case...',
        ];

        foreach ($cases as $key => $case) {
            $this->assertSame(
                'Default case...',
                trim($this->renderBlade($blade, ['i' => $key]))
            );
        }
    }

    public function test_empty()
    {
        $blade = <<<'BLADE'
        @switch($i)
        @endswitch
        BLADE;

        $this->assertSame(
            '',
            trim($this->renderBlade($blade))
        );
    }
}
