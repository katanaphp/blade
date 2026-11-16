<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class EnvironmentDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;


    public function testDefaultEnvironmentIsProduction(): void
    {
        $this->assertSame(
            'In production',
            $this->removeIndentation($this->renderBlade("@env('production') In production @endenv"))
        );
    }

    public function testCustomEnvironmentCallback(): void
    {
        $this->blade->setEnvironment(fn() => 'local');

        $this->assertSame(
            'In local',
            $this->removeIndentation($this->renderBlade("@env('local') In local @endenv"))
        );
    }

    public function testSupportsMultipleEnvironments(): void
    {
        $this->blade->setEnvironment(fn() => 'staging');

        $this->assertSame(
            'Non production environment',
            $this->removeIndentation($this->renderBlade("@env(['local', 'staging']) Non production environment @endenv"))
        );
    }

    public function testProductionDirective(): void
    {
        $this->blade->setEnvironment(fn() => 'production');

        $this->assertSame(
            'Production!!',
            $this->removeIndentation($this->renderBlade("@production Production!! @endproduction"))
        );

        $this->blade->setEnvironment(fn() => 'staging');

        $this->assertEmpty(
            $this->removeIndentation($this->renderBlade("@production Production!! @endproduction"))
        );
    }

    public function testDirectiveWithVariable()
    {
        $this->blade->setEnvironment(fn() => 'staging');

        $this->assertStringContainsString(
            'Staging environment',
            $this->renderBlade(
                '@env($env) Staging environment @endenv',
                ['env' => 'staging']
            )
        );
    }
}
