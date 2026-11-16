<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class IfBlockTest extends TestCase
{
    use VerifiesOutputTrait;

    public function test_basic_if()
    {
        $this->assertEquals(
            trim($this->renderBlade("@if(true) Hello @endif")),
            "Hello"
        );

        $this->assertEmpty(
            $this->renderBlade("@if(false) Hello @endif"),
        );

        $this->assertEquals(
            trim($this->renderBlade("@if(true) Hello @else Goodbye @endif")),
            "Hello"
        );

        $this->assertEquals(
            trim($this->renderBlade("@if(false) Hello @else Goodbye @endif")),
            "Goodbye"
        );
    }

    public function testSupportsMultipleParenthesis(): void
    {
        $this->assertEquals(
            trim($this->renderBlade("@if((true)) Hello @endif")),
            "Hello"
        );
    }


    public function testSupportsPhp(): void
    {
        $this->assertEquals(
            trim($this->renderBlade("@php echo 'Hello'; @endphp")),
            "Hello"
        );
    }

    public function testElseIfSupport(): void
    {
        $this->assertEquals(
            trim($this->renderBlade("@if(false) Hello @elseif(true) Goodbye @endif")),
            "Goodbye"
        );

        $this->assertEquals(
            trim($this->renderBlade("@if(false) Hello @elseif(false) Goodbye @endif")),
            ""
        );

        $this->assertEquals(
            trim($this->renderBlade("@if(false) Hello @elseif(false) Goodbye @else Hi @endif")),
            "Hi"
        );
    }

    public function testIssetWithoutSetting(): void
    {
        $this->assertEquals(
            trim($this->renderBlade('@isset($name) Hello @endisset')),
            ""
        );
    }

    public function testIssetWithSetting(): void
    {
        $this->assertEquals(
            trim($this->renderBlade(
                '@isset($name) Hello @endisset',
                ['name' => 'John']
            )),
            "Hello"
        );
    }

    public function testIssetWithElse(): void
    {
        $this->assertEquals(
            trim($this->renderBlade('@isset($name) Hello @else Goodbye @endisset')),
            "Goodbye"
        );
    }


    public function testIssetWithMultipleValues()
    {
        $blade = '@isset($firstName, $lastName) Hello @else Goodbye @endisset';
        $this->assertEquals(
            trim($this->renderBlade($blade)),
            "Goodbye"
        );

        $blade = '@isset($firstName, $lastName) {{ $firstName }} {{ $lastName }} @else Goodbye @endisset';
        $data = [
            'firstName' => 'Maria',
            'lastName' => 'Jose'
        ];

        $this->assertEquals(
            trim($this->renderBlade($blade, $data)),
            "Maria Jose"
        );
    }

    public function testEmpty()
    {
        $blade = '@empty($name) No name @endempty';

        $this->assertEquals(
            trim($this->renderBlade($blade)),
            "No name"
        );
    }

    public function testEmptyWithSetting()
    {
        $blade = '@empty($name) No name @endempty';

        $this->assertEquals(
            trim($this->renderBlade($blade, ['name' => 'John'])),
            ""
        );
    }

    public function testEmptyWithElse()
    {
        $blade = '@empty($name) No name @else Name is set @endempty';

        $this->assertEquals(
            trim($this->renderBlade($blade, ['name' => 'John'])),
            "Name is set"
        );
    }
}
