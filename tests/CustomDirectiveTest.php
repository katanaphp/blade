<?php

namespace Tests;

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
}
