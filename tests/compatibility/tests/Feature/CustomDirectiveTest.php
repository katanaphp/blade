<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use Tests\Support\ComparesWithKatana;

class CustomDirectiveTest extends TestCase
{
    use ComparesWithKatana;

    public function testDoesNotReceiveParenthesis(): void
    {
        $this->directive('customDirective', function (string $expression) {
            $this->assertStringStartsNotWith('(', $expression);
            $this->assertStringEndsNotWith(')', $expression);

            $this->assertSame('"Hello"', $expression);
        });

        $this->assertRendersSame('@customDirective("Hello")');
    }

    public function testParenthesisCalling(): void
    {
        $this->directive('withParenthesisNoParam', function (string $expression) {
            $this->assertEmpty($expression);
        });

        $this->assertRendersSame('@withParenthesisNoParam()');
        $this->assertRendersSame('@withParenthesisNoParam');
    }

    public function testTrimsMultipleParams(): void
    {
        $this->directive('nested', function (string $expression) {
            $this->assertSame('("egg")', $expression);
        });

        $this->assertRendersSame('@nested(("egg"))');
    }

    public function testDirectiveNameWithLettersOnly(): void
    {
        $this->directive('hello', function () {
            return "<?php echo 'world'; ?>";;
        });

        $this->assertRendersSame("@hello");
    }

    public function testDirectiveWithAlphaNumericChars(): void
    {
        $this->directive('1earth', function () {
            return "<?php echo 'cats'; ?>";
        });

        $this->assertRendersSame('@1earth');
    }

    public function testDirectiveSupportsUnderscore(): void
    {
        $this->directive('hello_planet', function () {
            return "<?php echo 'earth' ?>";
        });

        $this->assertRendersSame('@hello_planet');
    }

    public function testNamespacedDirective(): void
    {
        $this->directive('katanaphp::blade', function () {
            return "<?php echo 'sharp' ?>";
        });


        $this->assertRendersSame("@katanaphp::blade");
    }

    public function testNamespacedDirectiveSingleColon(): void
    {
        $this->assertThrowsSame(
            fn($blade) => $blade->directive('katanaphp:blade', function () {
                return "<?php echo 'sharp' ?>";
            })
        );
    }

    public function testDirectiveNameCaseSensitivity(): void
    {
        $this->directive('bluePlanet', function () {
            return "earth";
        });

        $this->assertRendersSame('@bluePlanet');
        $this->assertRendersSame('@blueplanet');
    }

    public function testHonoursLastRegistration()
    {
        $this->directive('first', fn() => 'First');
        $this->directive('first', fn() => 'Second');


        $this->assertRendersSame('@first');
    }
}
