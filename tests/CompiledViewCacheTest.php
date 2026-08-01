<?php

namespace Tests;

use Blade\Blade;
use Blade\Component;
use Blade\Config;
use Blade\FileSystemViewFinder;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blade::class)]
class CompiledViewCacheTest extends BladeTestCase
{
    protected string $basePath;

    #[Override]
    public function setup(): void
    {
        parent::setup();

        $this->basePath = sprintf('%s/katana-cache-test-%s', sys_get_temp_dir(), uniqid());

        foreach (['cache', 'views', 'overrides', 'components'] as $directory) {
            mkdir("{$this->basePath}/{$directory}", recursive: true);
        }
    }

    #[Override]
    public function tearDown(): void
    {
        $this->removeDirectory($this->basePath);

        parent::tearDown();
    }

    public function testViewsOfSameNameFromDifferentPathsDoNotShareCache(): void
    {
        /**
         * Both engines share a cache directory and identical
         * modified times, which a fresh checkout produces, so a
         * cache key of name and modified time alone would make
         * them read the same compiled file.
         */
        $this->createView('views/greeting.blade.php', 'Hello from views');
        $this->createView('overrides/greeting.blade.php', 'Hello from overrides');

        $default = new Blade("{$this->basePath}/views", "{$this->basePath}/cache");
        $override = new Blade("{$this->basePath}/overrides", "{$this->basePath}/cache");

        $this->assertSame('Hello from views', $default->render('greeting')->render());
        $this->assertSame('Hello from overrides', $override->render('greeting')->render());
    }

    public function testComponentDoesNotShareCacheWithViewOfSameName(): void
    {
        $this->createView('views/banner.blade.php', 'Banner view');
        $this->createView('components/banner.blade.php', 'Banner component');

        $blade = new Blade(config: (new Config("{$this->basePath}/cache"))
            ->addViewPath("{$this->basePath}/views")
            ->addAnonymousComponentViewFinder(
                new FileSystemViewFinder("{$this->basePath}/components")
            ));

        $component = new class('', 'banner', [], $blade) extends Component {};

        $this->assertSame('Banner view', $blade->render('banner')->render());
        $this->assertSame('Banner component', $blade->render($component)->render());
    }

    public function testCacheIsReusedForUnchangedViewAndInvalidatedOnChange(): void
    {
        $this->createView('views/page.blade.php', 'First');

        $blade = new Blade("{$this->basePath}/views", "{$this->basePath}/cache");

        $this->assertSame('First', $blade->render('page')->render());
        $this->assertCount(1, glob("{$this->basePath}/cache/*.php"));

        $this->createView('views/page.blade.php', 'Second', time() + 10);
        $this->assertSame('Second', $blade->render('page')->render());
    }

    /**
     * Modified times are pinned so the collision is deterministic rather
     * than dependent on how fast the test happens to run.
     */
    protected function createView(string $path, string $contents, ?int $modifiedTime = null): void
    {
        $path = "{$this->basePath}/{$path}";

        file_put_contents($path, $contents);
        touch($path, $modifiedTime ?? 1_700_000_000);
        clearstatcache(true, $path);
    }

    protected function removeDirectory(string $directory): void
    {
        foreach (glob("{$directory}/*") as $path) {
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}
