<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class DirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testMultipleLineDirective()
    {
        $this->assertStringContainsString(
            "Voted by Mail",
            $this->renderBlade(
                "@if(\$hasVoted && \n\$hasMailedVote ) Voted by Mail @endif",
                [
                    "hasVoted" => true,
                    "hasMailedVote" => true,
                ]
            )
        );
    }

    public function testCompilesContinue(): void
    {
        $this->assertStringContainsString(
            '',
            $this->renderBlade(
                '@foreach($items as $item) @continue {{ $item }} @endforeach',
                ['items' => [1, 2, 3]]
            )
        );
    }
}
