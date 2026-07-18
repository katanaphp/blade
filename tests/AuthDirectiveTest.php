<?php

namespace Tests;

use Blade\Exceptions\BladeException;
use Blade\Messages;
use PHPUnit\Framework\TestCase;

class AuthDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testAuthDirectiveThrowsExceptionWhenCallbackNotSet(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_AUTH_CALLBACK_REQUIRED);

        $this->renderBlade('@auth Authenticated @endauth');
    }

    public function testAuthDirectiveRendersWhenTrue(): void
    {
        $this->blade->config->setAuth(fn() => true);

        $this->assertSame(
            'Authenticated',
            $this->removeIndentation(
                $this->renderBlade('@auth Authenticated @endauth')
            )
        );

        $this->blade->config->setAuth(fn() => false);

        $this->assertEmpty($this->renderBlade('@auth Authenticated @endauth'));
    }

    public function testGuestDirectiveThrowsExceptionWhenCallbackNotSet(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_AUTH_CALLBACK_REQUIRED);

        $this->renderBlade('@guest Authenticated @endguest');
    }

    public function testGuestDirectiveRendersWhenFalse(): void
    {
        $this->blade->config->setAuth(fn() => false);

        $this->assertSame(
            'Guest',
            $this->removeIndentation($this->renderBlade('@guest Guest @endguest'))
        );

        $this->blade->config->setAuth(fn() => true);

        $this->assertEmpty($this->renderBlade('@guest Guest @endguest'));
    }

    public function testAuthDirectiveReceivesParameters(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage("User role is admin and is performing dance");

        $this->blade->config->setAuth(
            fn(string $role, string $action) => throw new BladeException("User role is {$role} and is performing {$action}")
        );

        $this->renderBlade("@auth('admin', 'dance') Dancing @endauth");
    }

    public function testGuestDirectiveReceivesParameters(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage("Guest is looking for cart items and price");

        $this->blade->config->setAuth(
            fn(string $thing_1, string $thing_2) => throw new BladeException("Guest is looking for {$thing_1} and {$thing_2}")
        );

        $this->renderBlade("@guest('cart items', 'price') Dancing @endguest");
    }
}
