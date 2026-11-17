<?php

namespace Tests\Support;

use Blade\Blade as Katana;
use Blade\Config;
use Closure;
use Illuminate\Support\Facades\Blade as Laravel;
use Illuminate\Support\Facades\File;
use Illuminate\View\Compilers\BladeCompiler;
use Throwable;

/**
 * Renders a template with Laravel Blade and with Katana
 * and compares the results, so Laravel's output is the
 * expectation instead of a string literal.
 */
trait ComparesWithKatana
{
    protected Katana $katana;

    private InMemoryViewFinder $katanaViews;

    /**
     * Called by Laravel's test case for every test
     * using this trait.
     */
    protected function setUpComparesWithKatana(): void
    {
        /**
         * Both engines cache compiled views by template, a directive
         * registered by an earlier test must not leak into this one.
         */
        $this->artisan('view:clear');

        $cachePath = storage_path('framework/testing/katana');

        File::deleteDirectory($cachePath);
        File::ensureDirectoryExists($cachePath);

        $this->katanaViews = new InMemoryViewFinder();
        $this->katana = new Katana(
            config: (new Config($cachePath))->addViewFinder($this->katanaViews)
        );
    }

    /**
     * Asserts that Laravel and Katana render the template to
     * the same output, ignoring differences in whitespace that
     * do not change the resulting markup. Returns Laravel's output.
     */
    protected function assertRendersSame(string $template, array $data = [], bool $normalizeWhitespace = true): string
    {
        $expected = $this->renderLaravel($template, $data);
        $actual = $this->renderKatana($template, $data);

        if ($normalizeWhitespace) {
            $expected = $this->normalizeWhitespace($expected);
            $actual = $this->normalizeWhitespace($actual);
        }

        $this->assertSame(
            $expected,
            $actual,
            "Katana does not render the template like Laravel:\n{$template}"
        );

        return $expected;
    }

    /**
     * Asserts that Laravel and Katana render the template to
     * byte for byte the same output. Returns that output.
     */
    protected function assertRendersIdentical(string $template, array $data = []): string
    {
        return $this->assertRendersSame($template, $data, normalizeWhitespace: false);
    }

    /**
     * Asserts that an action Laravel rejects is rejected by Katana
     * with the same kind of exception. The action runs once per
     * engine and receives it, the messages are not compared.
     *
     * @param Closure(BladeCompiler|Katana): mixed $action
     */
    protected function assertThrowsSame(Closure $action): void
    {
        $expected = $this->catchThrowable(fn() => $action(Laravel::getFacadeRoot()));
        $actual = $this->catchThrowable(fn() => $action($this->katana));

        $this->assertNotNull($expected, 'Laravel did not throw.');
        $this->assertNotNull($actual, sprintf('Katana did not throw, Laravel throws %s.', $expected::class));

        $this->assertInstanceOf(
            $expected::class,
            $actual,
            'Katana does not throw the same kind of exception as Laravel.'
        );
    }

    private function catchThrowable(Closure $action): ?Throwable
    {
        try {
            $action();
        } catch (Throwable $throwable) {
            return $throwable;
        }

        return null;
    }

    /**
     * Collapses every run of whitespace into a single space,
     * which is how a browser treats it outside of <pre>.
     */
    private function normalizeWhitespace(string $output): string
    {
        return trim(preg_replace('/\s+/', ' ', $output));
    }

    protected function renderLaravel(string $template, array $data = []): string
    {
        return Laravel::render($template, $data);
    }

    protected function renderKatana(string $template, array $data = []): string
    {
        return (string) $this->katana->render(
            $this->katanaViews->add($template),
            $data
        );
    }

    /**
     * Registers a custom directive on both engines.
     */
    protected function directive(string $name, Closure $callback): void
    {
        Laravel::directive($name, $callback);
        $this->katana->directive($name, $callback);
    }

    /**
     * Registers a stringable handler on both engines.
     */
    protected function stringable(Closure $callback): void
    {
        Laravel::stringable($callback);
        $this->katana->stringable($callback);
    }
}
