<?php

namespace Tests;

use Blade\Exceptions\BladeException;
use Blade\Messages;
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

    public function testSupportsCombinedFlags(): void
    {
        $this->assertSame(
            "[\n    1,\n    2\n]",
            $this->renderBlade('@json($content, JSON_PRETTY_PRINT | JSON_HEX_TAG, 512)', ['content' => [1, 2]])
        );
    }

    public function testSupportsNumericFlags(): void
    {
        $this->assertSame(
            "[\n    1,\n    2\n]",
            $this->renderBlade('@json($content, 128)', ['content' => [1, 2]])
        );

        $this->assertSame(
            "[\n    1,\n    2\n]",
            $this->renderBlade('@json($content, JSON_HEX_TAG | 128)', ['content' => [1, 2]])
        );
    }

    public function testAcceptsDepthAtMinimumAndMaximum(): void
    {
        $this->assertSame('1', $this->renderBlade('@json($content, JSON_HEX_TAG, 1)', ['content' => 1]));
        $this->assertSame('[1]', $this->renderBlade('@json($content, JSON_HEX_TAG, 2147483647)', ['content' => [1]]));
    }

    public function testThrowsOnInvalidFlags(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_FLAGS, 'JSON_PRETY_PRINT'));

        $this->renderBlade('@json($content, JSON_PRETY_PRINT)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsOnInvalidFlagInsideCombination(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_FLAGS, 'JSON_HEX_TAG | JSON_NOPE'));

        $this->renderBlade('@json($content, JSON_HEX_TAG | JSON_NOPE)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsOnConstantThatIsNotAJsonFlag(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_FLAGS, 'PHP_INT_MAX'));

        $this->renderBlade('@json($content, PHP_INT_MAX)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsOnInvalidDepth(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_DEPTH, 'deep', 1, 2147483647));

        $this->renderBlade('@json($content, JSON_HEX_TAG, deep)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsOnDepthBelowMinimum(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_DEPTH, '0', 1, 2147483647));

        $this->renderBlade('@json($content, JSON_HEX_TAG, 0)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsOnDepthAboveMaximum(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_DEPTH, '2147483648', 1, 2147483647));

        $this->renderBlade('@json($content, JSON_HEX_TAG, 2147483648)', ['content' => [1, 2, 3]]);
    }

    public function testThrowsWhenDataContainsComma(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_INVALID_JSON_FLAGS, '["{x}" => 1])'));

        $this->renderBlade('@json(__("key", ["{x}" => 1]))');
    }
}
