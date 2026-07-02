<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
class ComponentAttributeTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testExpressionAttributeWithValue()
    {
        $this->createComponent('alert', '<div>Hello, {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, Taylor</div>",
            $this->renderBlade('<x-alert name="Taylor" />')
        );
    }

    public function testExpressionAttributeWithVariable()
    {
        $this->createComponent('alert', '<div>Hello, {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, Taylor</div>",
            $this->renderBlade('<x-alert name="$name" />', ['name' => 'Taylor'])
        );
    }

    public function testExpressionAttributeWithValueAndVariable()
    {
        $this->createComponent('alert', '<div>Hello, {{ $name }}</div>');

        $blade = <<<'BLADE'
        <x-alert :name="$name . ' Tamagui'" />
        BLADE;

        $this->assertSame(
            "<div>Hello, Taylor Tamagui</div>",
            $this->renderBlade($blade, ['name' => 'Taylor'])
        );
    }

    public function testExpressionAttributeWithSingleQuotes()
    {
        $this->createComponent('alert', '<div>Hello, {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, Taylor</div>",
            $this->renderBlade("<x-alert :name='\$name' />", ['name' => 'Taylor'])
        );
    }

    public function testExpressionAttributeWithDoubleQuotes()
    {
        $this->createComponent('alert', '<div {{ $attributes }}></div>');

        $this->assertSame(
            "<div name='Hello, Taylor'></div>",
            $this->renderBlade('<x-alert name="Hello, {{ $name }}" />', ['name' => 'Taylor'])
        );
    }

    public function testMultiLineProps(): void
    {
        $this->createComponent(
            'alert',
            '@props([
                "type" => "info",
                "message" => "Everything is going well",
                "time" => time(),
            ])
            {{ $message }} at {{ $time }}',
        );


        $this->assertStringContainsString(
            'Everything is going well at ' . time(),
            $this->renderBlade('<x-alert/>')
        );
    }
}
