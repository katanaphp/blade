<?php

namespace Tests;

use Blade\FileSystemViewFinder;
use Blade\ViewFinder;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class StringableTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testImplementation(): void
    {
        $this->blade->stringable(function (ViewFinder $finder) {
            return $finder->identifier();
        });

        $this->assertSame("Hello world", $this->renderBlade('{{ $finder }}', [
            'finder' => new class('Hello world') extends FileSystemViewFinder {}
        ]));
    }

    public function testDateStringable(): void
    {
        $outputFormat = '\W\e\e\k W, Y';
        $this->blade->stringable(fn(DateTime $d) => $d->format($outputFormat));

        $date = new DateTime();

        $this->assertSame(
            $date->format($outputFormat),
            $this->renderBlade('{{$date}}', ['date' => $date])
        );
    }

    public function testIntersectionStringable(): void
    {
        $outputFormat = '\W\e\e\k W, Y';
        $this->blade->stringable(fn(DateTimeInterface|DateTime $d) => $d->format($outputFormat));

        $date = new DateTime();

        $this->assertSame(
            $date->format($outputFormat),
            $this->renderBlade('{{$date}}', ['date' => $date])
        );
    }
}
