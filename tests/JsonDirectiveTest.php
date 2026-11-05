<?php

namespace Tests;

use JsonException;
use PHPUnit\Framework\TestCase;

class JsonDirectiveTest  extends TestCase
{
    use VerifiesOutputTrait;
    public function testDirectiveIsRegistered(): void
    {
        $this->assertStringNotContainsString(
            '@json',
            $this->renderBlade('@json($data)', ['data' => []])
        );
    }

    public function testBasicArray(): void
    {
        $this->assertSame(
            '[1,2,3]',
            $this->renderBlade('@json($json)', ['json' => [1, 2, 3]])
        );
    }

    public function testBasicObject(): void
    {
        $object = (object)[
            'name' => 'Manuel',
            'age' => 'infinity'
        ];

        $this->assertSame(
            '{"name":"Manuel","age":"infinity"}',
            $this->renderBlade('@json($json)', ['json' => $object])
        );
    }

    public function testConvertsSingleQuotesToHex(): void
    {
        $object = (object) ['name' => 'Manuel\'s Cookies'];

        $this->assertSame(
            '{"name":"Manuel\u0027s Cookies"}',
            $this->renderBlade('@json($content)', ['content' => $object])
        );
    }

    public function testConvertsAmpsToHex(): void
    {
        $object = (object) ['name' => 'Milk & Cookies'];

        $this->assertSame(
            '{"name":"Milk \u0026 Cookies"}',
            $this->renderBlade('@json($content)', ['content' => $object])
        );
    }

    public function testConvertsAngleBracketsToHex(): void
    {
        $this->assertSame(
            '"\u003C\/script\u003E"',
            $this->renderBlade('@json($content)', ['content' => "</script>"])
        );

        $this->assertSame(
            '"\u003Cscript\u003E"',
            $this->renderBlade('@json($content)', ['content' => "<script>"])
        );
    }

    public function testConvertsDoubleQuotesToHex(): void
    {
        $this->assertSame(
            '"Double quotes \u0022\u0022"',
            $this->renderBlade('@json($content)', ['content' => 'Double quotes ""'])
        );
    }

    public function testSupportsFlagsParam(): void
    {
        $this->assertSame(
            <<<'DOC'
            [
                1,
                2,
                3
            ]
            DOC,
            $this->renderBlade('@json($content, JSON_PRETTY_PRINT)', ['content' => [1, 2, 3]])
        );
    }

    public function testSupportsDepthParam(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $this->renderBlade(
            '@json($content, JSON_THROW_ON_ERROR, 2)',
            [
                'content' => [[[]]]
            ]
        );
    }
}
