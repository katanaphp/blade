<?php

namespace Tests;

use Blade\Config;
use Blade\FileSystemViewFinder;
use Blade\Messages;
use Blade\ViewFinder;
use DateTime;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StringableTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testStringableOutput(): void
    {
        $outputFormat = '\W\e\e\k W, Y';
        $this->blade->stringable(fn(DateTime $d) => $d->format($outputFormat));

        $date = new DateTime();

        $this->assertSame(
            $date->format($outputFormat),
            $this->renderBlade('{{ $date }}', ['date' => $date])
        );
    }

    public function testStringableWithUnescapedOutput(): void
    {
        $output = "<b>Bold</b>";

        $this->blade->stringable(fn(FileSystemViewFinder $_) => $output);

        $this->assertSame(
            htmlentities($output),
            $this->renderBlade(
                '{{ $finder }}',
                ['finder' => new FileSystemViewFinder('/')]
            )
        );

        $this->assertSame(
            $output,
            $this->renderBlade(
                '{!! $finder !!}',
                ['finder' => new FileSystemViewFinder('/')]
            )
        );
    }

    public function testStringableUsesLastRegisteredClosure(): void
    {
        $this->blade->stringable(fn(FileSystemViewFinder $_) => 'First finder');
        $this->blade->stringable(fn(FileSystemViewFinder $_) => 'Second finder');

        $this->assertSame(
            'Second finder',
            $this->renderBlade(
                '{{ $finder }}',
                ['finder' => new FileSystemViewFinder('/')]
            )
        );
    }

    public function testClosureWithInterfaceDoesNotInterceptClass(): void
    {
        $this->blade->stringable(fn(ViewFinder $_) => '');

        $this->assertSame(
            sprintf(Messages::ERROR_CANNOT_CAST_TO_STRING, 'object'),
            $this->renderBlade(
                '{{ $finder }}',
                ['finder' => new FileSystemViewFinder('/')]
            )
        );
    }

    public function testClosureWithIntersectionTypesAreNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Messages::ERROR_INVALID_STRINGABLE_PARAM);

        $this->blade->stringable(fn(ViewFinder|Config $_) => '');
    }
}
