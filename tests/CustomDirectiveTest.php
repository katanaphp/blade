<?php

namespace Tests;

use DateInterval;
use DatePeriod;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CustomDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testRegisteredDirective(): void
    {
        $this->blade->directive('date', function (string $expression) {
            $code = $expression ? "date({$expression})" : "date('Y-m-d')";

            return "<?php echo {$code}; ?>";
        });

        $this->assertSame(
            date('Y-m-d'),
            $this->renderBlade("@date")
        );

        $this->assertSame(
            date('Y'),
            $this->renderBlade("@date('Y')")
        );
    }

    public function testWorksWithPassedValues(): void
    {
        $this->blade->directive('date', function (string $expression) {
            $code = "($expression)->format('Y-m-d')";

            return "<?php echo {$code}; ?>";
        });

        $this->assertSame(
            date('Y-m-d', time() - 86400),
            $this->renderBlade("@date(\$date)", [
                'date' => (new DateTime)->sub(new DateInterval("P1D"))
            ])
        );
    }

    public function testDoesNotReceiveParenthesis(): void
    {
        $this->blade->directive('customDirective', function (string $expression) {
            $this->assertSame('"Hello"', $expression);
        });

        $this->renderBlade('@customDirective("Hello")');
    }

    public function testPreservesNestedParams(): void
    {
        $this->blade->directive('nested', function (string $expression) {
            $this->assertSame('("egg")', $expression);
        });

        $this->renderBlade('@nested(("egg"))');
    }

    public function testParenthesisCalling(): void
    {
        $this->blade->directive('withParenthesisNoParam', function (string $expression) {
            $this->assertEmpty($expression);
        });

        $this->renderBlade('@withParenthesisNoParam()');
        $this->renderBlade('@withParenthesisNoParam');
    }

    public function testDirectiveNameWithLettersOnly(): void
    {
        $this->blade->config->directive('hello', function () {
            return "<?php echo 'world'; ?>";
        });

        $this->assertSame("world", $this->renderBlade("@hello"));
    }

    public function testDirectiveWithAlphaNumericChars(): void
    {
        $this->blade->config->directive('1bluePlanet', function () {
            return "<?php echo 'earth'; ?>";
        });

        $this->assertSame('earth', $this->renderBlade('@1bluePlanet'));
    }

    public function testDirectiveSupportsUnderscore(): void
    {
        $this->blade->directive('name_with_underscores', function () {
            return "<?php echo 'earth' ?>";
        });

        $this->assertSame('earth', $this->renderBlade('@name_with_underscores'));
    }

    public function testNamespacedDirective(): void
    {
        $this->blade->directive('katanaphp::blade', function () {
            return "<?php echo 'sharp' ?>";
        });
        $this->assertSame('sharp', $this->renderBlade("@katanaphp::blade"));
    }

    public function testNamespacedDirectiveSingleColon(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->blade->directive('katanaphp:blade', function () {
            return "<?php echo 'sharp' ?>";
        });
    }

    public function testDirectiveNameAreCaseSensitive(): void
    {
        $this->blade->directive('bluePlanet', function () {
            return "earth";
        });

        $this->assertSame('earth', $this->renderBlade('@bluePlanet'));
        $this->assertSame('@blueplanet', $this->renderBlade('@blueplanet'));
    }
}
