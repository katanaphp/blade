<?php

use Blade\Exceptions\BladeException;
use Blade\FileSystemViewFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use Tests\BladeTestCase;

#[Small]
#[CoversClass(FileSystemViewFinder::class)]
class FileSystemViewFinderTest extends BladeTestCase
{
    protected string $basePath;
    protected FileSystemViewFinder $viewFinder;

    protected array $files = [];

    #[Override]
    public function setup(): void
    {
        parent::setup();

        $this->basePath = sys_get_temp_dir();
        $this->viewFinder = new FileSystemViewFinder($this->basePath);
    }

    #[Override]
    public function tearDown(): void
    {
        array_walk($this->files, unlink(...));

        parent::tearDown();
    }

    public function testTrailingSlashRemovedFromBasePath(): void
    {
        $finder = new FileSystemViewFinder($this->basePath . '/views/');

        $this->assertSame(
            $this->basePath . '/views',
            $finder->basePath,
        );
    }

    public function testReturnsTrueWhenViewExists(): void
    {
        $this->createFile('greeting.blade.php', 'Hello');

        $this->assertTrue($this->viewFinder->viewExists('greeting'));
    }

    public function testReturnsFalseWhenViewDoesNotExist(): void
    {
        $this->assertFalse($this->viewFinder->viewExists('missing-view'));
    }

    public function testResolvesDotToSlashInName(): void
    {
        $this->createFile('components/card.blade.php', '<div></div>');

        $this->assertTrue($this->viewFinder->viewExists('components.card'));
    }

    public function testRetrievesContents(): void
    {
        $contents = sprintf("Hello there %s", time());

        $this->createFile('index.blade.php', $contents);

        $this->assertSame(
            $contents,
            $this->viewFinder->getContents('index')
        );
    }

    public function testRetrievesNestedViews(): void
    {
        $contents = sprintf("Hello internet %s", time());

        $this->createFile('pages/about/katana.blade.php', $contents);

        $this->assertSame(
            $contents,
            $this->viewFinder->getContents('pages.about.katana')
        );
    }

    public function testRetrievesFileMTime(): void
    {
        $time = time() - 800;

        touch($this->basePath . '/index.blade.php', $time);

        $this->assertSame(
            $time,
            $this->viewFinder->lastModified('index')
        );
    }

    public function testRetrievesFileMTimeNestedViews(): void
    {
        $time = time() - 800;

        $this->touchFile('countries/germany.blade.php', $time);

        $this->assertSame(
            $time,
            $this->viewFinder->lastModified('countries.germany')
        );
    }

    public function testThrowsOnEmptyViewName(): void
    {
        $this->expectException(BladeException::class);
        $this->viewFinder->viewExists('');
    }

    public function testThrowsWhenGettingContentsOfMissingView(): void
    {
        $this->expectException(BladeException::class);
        $this->viewFinder->getContents('nonexistent');
    }

    public function testThrowsWhenGettingMTimeOfMissingView(): void
    {
        $this->expectException(BladeException::class);
        $this->viewFinder->lastModified('nonexistent');
    }

    protected function touchFile(string $name, int $modifiedTime): void
    {
        $path = sprintf('%s/%s', $this->basePath, $name);

        $this->createDirectory($path);

        touch($path, $modifiedTime);
    }

    protected function createFile(string $name, string $content = ''): void
    {
        $path = sprintf('%s/%s', $this->basePath, $name);

        $this->createDirectory($path);

        file_put_contents($path, $content);
    }

    /**
     * Recursively creates parent directory for a file path.
     */
    protected function createDirectory(string $filePath): void
    {
        $directory = str_replace(pathinfo($filePath, PATHINFO_BASENAME), '', $filePath);

        if (!is_dir($directory) && !mkdir($directory, recursive: true)) {
            throw new Exception("Unable to create directory {$directory}");
        }
    }
}
