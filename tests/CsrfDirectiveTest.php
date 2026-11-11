<?php

namespace Tests;

use Blade\Messages;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CsrfDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testThrowsWithoutCallbackRegistration(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Messages::ERROR_CSRF_CALLBACK_REQUIRED);

        $this->renderBlade('@csrf');
    }

    public function testOutputsContentsFromContent(): void
    {
        $output = sprintf("<input type='hidden' name='_csrf' value='%s'/>", time());

        $this->blade->config->setCsrfCallback(fn() => $output);

        $this->assertSame(
            $output,
            $this->renderBlade('@csrf')
        );
    }
}
