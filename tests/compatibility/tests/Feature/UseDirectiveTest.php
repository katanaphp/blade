<?php

namespace Tests\Feature;

use Tests\Support\ComparesWithKatana;
use Tests\TestCase;

use function App\Helpers\format_money;

class UseDirectiveTest extends TestCase
{
    use ComparesWithKatana;

    public function testDirective(): void
    {
        $template = "@use(App\Models\User) {{ User::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithString(): void
    {
        $template = "@use('App\Models\User') {{ User::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithInlineAlias(): void
    {
        $template = "@use(App\Models\User as Monsters) {{ Monsters::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithInlineAliasAsString(): void
    {
        $template = "@use('App\Models\User as Monsters') {{ Monsters::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithMultipleImports(): void
    {
        $template = "@use(App\Models\{User, House}) {{ House::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithMultipleImportsAsString(): void
    {
        $template = "@use('App\Models\{User, House}') {{ House::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithMultipleParams(): void
    {
        $this->markTestSkipped('Not supported');

        $template = "@use(App\Models\User, App\Models\House) {{ House::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithMultipleImportsWithAlias(): void
    {
        $template = "@use(App\Models\{User as Monster, House as Haunted}) {{ Haunted::class }}";

        $this->assertRendersSame($template);
    }

    public function testDirectiveWithMultipleImportsWithAliasAsString(): void
    {
        $template = "@use('App\Models\{User as Monster, House as Haunted}') {{ Haunted::class }}";

        $this->assertRendersSame($template);
    }

    public function testAliasingWithSecondParam(): void
    {
        $template = "@use(App\Models\House, Haunted) {{ Haunted::class }}";

        $this->assertRendersSame($template);
    }

    public function testAliasingWithSecondParamAsString(): void
    {
        $template = "@use(App\Models\House, 'Haunted') {{ Haunted::class }}";

        $this->assertRendersSame($template);
    }

    public function testAliasingWithFirstAndSecondParam(): void
    {
        $this->markTestSkipped('Not supported');
        $template = "@use(App\Models\{House, User}, 'Haunted') {{ Haunted::class }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImport(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use(function App\Helpers\\format_money) {{ format_money() }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImportAsString(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use('function App\Helpers\\format_money') {{ format_money() }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImportInlineAlias(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use(function App\Helpers\\format_money as pretty_money) {{ pretty_money() }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImportInlineAliasAsString(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use('function App\Helpers\\format_money as pretty_money') {{ pretty_money() }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImportInlineAliasWithSecondParam(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use(function App\Helpers\\format_money, pretty_money) {{ pretty_money() }}";

        $this->assertRendersSame($template);
    }

    public function testFunctionImportInlineAliasWithSecondParamAsString(): void
    {
        $this->assertSame('INR', format_money());

        $template = "@use(function App\Helpers\\format_money, 'pretty_money') {{ pretty_money() }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImport(): void
    {
        $template = "@use(const App\Helpers\\CURRENCY) {{ CURRENCY }}";

        $this->assertRendersSame($template);
    }

    public function testTrimsQuotes(): void
    {
        $template = "@use(''const App\Helpers\\CURRENCY'') {{ CURRENCY }}";

        $this->assertRendersSame($template);
    }

    public function testTrimsQuotesMismatch(): void
    {
        $template = "@use('\"const App\Helpers\\CURRENCY\"') {{ CURRENCY }}";

        $this->assertRendersSame($template);
    }

    public function testTrimsQuotedSpaceMismatch(): void
    {
        $template = "@use('\"  const App\Helpers\\CURRENCY  \"') {{ CURRENCY }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImportAsString(): void
    {
        $template = "@use('const App\Helpers\\CURRENCY') {{ CURRENCY }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImportInlineAlias(): void
    {
        $template = "@use(const App\Helpers\\CURRENCY as MONEY) {{ MONEY }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImportInlineAliasAsString(): void
    {
        $template = "@use('const App\Helpers\\CURRENCY as MONEY') {{ MONEY }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImportWithSecondParam(): void
    {
        $template = "@use(const App\Helpers\\CURRENCY, MONEY) {{ MONEY }}";

        $this->assertRendersSame($template);
    }

    public function testConstantImportWithSecondParamAsString(): void
    {
        $template = "@use(const App\Helpers\\CURRENCY, 'MONEY') {{ MONEY }}";

        $this->assertRendersSame($template);
    }
}
