<?php

namespace Tests;

use PHPUnit\Framework\TestBuilder;
use PHPUnit\Framework\TestCase;

class UseDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testDirective(): void
    {
        $template = "@use(Symfony\Component\VarDumper\VarDumper)\n" .
            '{{ VarDumper::class }}';

        $this->assertSame(
            'Symfony\Component\VarDumper\VarDumper',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testWithLeadingSlash(): void
    {
        $template = "@use(\Symfony\Component\VarDumper\VarDumper)\n" .
            '{{ VarDumper::class }}';

        $this->assertSame(
            'Symfony\Component\VarDumper\VarDumper',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testWithValueAsString(): void
    {
        $template = "@use('Symfony\Component\VarDumper\VarDumper')\n" .
            '{{ VarDumper::class }}';

        $this->assertSame(
            'Symfony\Component\VarDumper\VarDumper',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testAlias(): void
    {
        $template = "@use(Symfony\Component\VarDumper\VarDumper, 'Printer')\n" . '{{ Printer::class }}';

        $this->assertSame(
            'Symfony\Component\VarDumper\VarDumper',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithString(): void
    {
        $template = "@use('PHPUnit\Framework\TestCase') {{ TestCase::class }}";

        $this->assertSame(
            TestCase::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithInlineAlias(): void
    {
        $template = "@use(PHPUnit\Framework\TestCase as Monsters) {{ Monsters::class }}";

        $this->assertSame(
            TestCase::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithInlineAliasAsString(): void
    {
        $template = "@use('PHPUnit\Framework\TestCase as Monsters') {{ Monsters::class }}";

        $this->assertSame(
            TestCase::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithMultipleImports(): void
    {
        $template = "@use(PHPUnit\Framework\{TestCase, TestBuilder}) {{ TestCase::class }} {{ TestBuilder::class }}";

        $this->assertSame(
            TestCase::class . " " . TestBuilder::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithMultipleImportsAsString(): void
    {
        $template = "@use('PHPUnit\Framework\{TestCase, TestBuilder}') {{ TestCase::class }} {{ TestBuilder::class }}";

        $this->assertSame(
            TestCase::class . " " . TestBuilder::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithMultipleImportsWithAlias(): void
    {
        $template = "@use(PHPUnit\Framework\{TestCase as Scenario, TestBuilder as Bob}) {{ Scenario::class }} {{ Bob::class }}";

        $this->assertSame(
            TestCase::class . " " . TestBuilder::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testDirectiveWithMultipleImportsWithAliasAsString(): void
    {
        $template = "@use('PHPUnit\Framework\{TestCase as Scenario, TestBuilder as Bob}') {{ Scenario::class }} {{ Bob::class }}";

        $this->assertSame(
            TestCase::class . " " . TestBuilder::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testAliasingWithSecondParam(): void
    {
        $template = "@use(PHPUnit\Framework\TestCase, Scenario) {{ Scenario::class }}";

        $this->assertSame(
            TestCase::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testAliasingWithSecondParamAsString(): void
    {
        $template = "@use(PHPUnit\Framework\TestCase, 'Scenario') {{ Scenario::class }}";

        $this->assertSame(
            TestCase::class,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImport(): void
    {

        $template = "@use(function Blade\\e) {{ e('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImportAsString(): void
    {
        $template = "@use('function Blade\\e') {{ e('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImportInlineAlias(): void
    {
        $template = "@use(function Blade\\e as output_mode) {{ output_mode('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImportInlineAliasAsString(): void
    {
        $template = "@use('function Blade\\e as output_mode') {{ output_mode('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImportInlineAliasWithSecondParam(): void
    {
        $template = "@use('function Blade\\e', output_mode) {{ output_mode('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testFunctionImportInlineAliasWithSecondParamAsString(): void
    {
        $template = "@use(function Blade\\e, 'output_mode') {{ output_mode('Hello') }}";

        $this->assertSame(
            "Hello",
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantDefault(): void
    {
        $template = "{{ defined('NAME') ? 'yes' : 'no' }}";

        $this->assertSame(
            'no',
            $this->removeIndentation($this->renderBlade($template))
        );
    }
    public function testConstantImport(): void
    {
        $template = "@use(const Tests\\NAME) {{ NAME }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantImportAsString(): void
    {

        $template = "@use('const Tests\\NAME') {{ NAME }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantImportInlineAlias(): void
    {
        $template = "@use(const Tests\\NAME as NOMBRE) {{ NOMBRE }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantImportInlineAliasAsString(): void
    {
        $template = "@use('const Tests\\NAME as NOMBRE') {{ NOMBRE }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantImportWithSecondParam(): void
    {
        $template = "@use(const Tests\\NAME, NOMBRE) {{ NOMBRE }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testConstantImportWithSecondParamAsString(): void
    {
        $template = "@use(const Tests\\NAME, 'NOMBRE') {{ NOMBRE }}";

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testInComponent(): void
    {
        $this->createComponent('alert', '@props(["name"]) @use(const Tests\\NAME as NOMBRE_ES) {{ NOMBRE_ES }}');

        $this->assertSame(
            NAME,
            $this->removeIndentation($this->renderBlade('<x-alert/>'))
        );
    }
}
