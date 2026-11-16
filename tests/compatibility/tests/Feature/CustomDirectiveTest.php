<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Override;
use Tests\TestCase;

class CustomDirectiveTest extends TestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
    }
    public function testDoesNotReceiveParenthesis(): void
    {
        Blade::directive('customDirective', function (string $expression) {
            $this->assertStringStartsNotWith('(', $expression);
            $this->assertStringEndsNotWith(')', $expression);

            $this->assertSame('"Hello"', $expression);
        });

        Blade::render('@customDirective("Hello")');
    }

    public function testParenthesisCalling(): void
    {
        Blade::directive('withParenthesisNoParam', function (string $expression) {
            $this->assertEmpty($expression);
        });

        Blade::render('@withParenthesisNoParam()');
        Blade::render('@withParenthesisNoParam');
    }

    public function testTrimsMultipleParams(): void
    {
        Blade::directive('nested', function (string $expression) {
            $this->assertSame('("egg")', $expression);
        });

        Blade::render('@nested(("egg"))');
    }

    public function testDirectiveNameWithLettersOnly(): void
    {
        Blade::directive('hello', function () {
            return "<?php echo 'world'; ?>";;
        });

        $this->assertSame("world", Blade::render("@hello"));
    }

    public function testDirectiveWithAlphaNumericChars(): void
    {
        Blade::directive('1earth', function () {
            return "<?php echo 'cats'; ?>";
        });

        $this->assertSame('cats', Blade::render('@1earth'));
    }

    public function testDirectiveSupportsUnderscore(): void
    {
        Blade::directive('hello_planet', function () {
            return "<?php echo 'earth' ?>";
        });

        $this->assertSame('earth', Blade::render('@hello_planet'));
    }

    public function testNamespacedDirective(): void
    {
        Blade::directive('katanaphp::blade', function () {
            return "<?php echo 'sharp' ?>";
        });


        $this->assertSame('sharp', Blade::render("@katanaphp::blade"));
    }

    public function testNamespacedDirectiveSingleColon(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Blade::directive('katanaphp:blade', function () {
            return "<?php echo 'sharp' ?>";
        });
    }

    public function testDirectiveNameCaseSensitivity(): void
    {
        Blade::directive('bluePlanet', function () {
            return "earth";
        });

        $this->assertSame('earth', Blade::render('@bluePlanet'));
        $this->assertSame('@blueplanet', Blade::render('@blueplanet'));
    }

    public function testHonoursLastRegistration()
    {
        Blade::directive('first', fn() => 'First');
        Blade::directive('first', fn() => 'Second');


        $this->assertSame('Second', Blade::render('@first'));
    }
}
