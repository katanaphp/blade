<?php

namespace Tests\Config;

use Blade\Config;
use Blade\Exceptions\BladeException;
use Blade\FileSystemViewFinder;
use Blade\Messages;
use Blade\ViewFinder;
use Exception;
use Override;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testRemovesTrailingSlashFromCachePath(): void
    {
        $config = new Config(cachePath: '/var/cache/');

        $this->assertSame('/var/cache', $config->cachePath);
    }

    public function testAddViewPathAddsFileSystemLoader(): void
    {
        $viewDirectory = __DIR__;

        $config = $this->getConfig()
            ->addViewPath($viewDirectory);

        $this->assertCount(1, $config->getViewFinders());

        /**
         * @var FileSystemViewFinder
         */
        $viewFinder = $config->getViewFinders()[0];

        $this->assertInstanceOf(FileSystemViewFinder::class, $viewFinder);
        $this->assertSame($viewDirectory, $viewFinder->basePath);
    }

    public function testAddViewFinderAppendsEntries(): void
    {
        $config = $this->getConfig();

        $this->assertSame(0, count($config->getViewFinders()));

        $finders = [
            $this->getDummyViewFinder(),
            $this->getDummyViewFinder(),
        ];

        array_walk($finders, fn($finder) => $config->addViewFinder($finder));

        $this->assertCount(count($finders), $config->getViewFinders());
        $this->assertEquals($finders, $config->getViewFinders());
    }

    public function testAddAnonymousComponentPathRegistersFileSystemViewFinder(): void
    {
        $componentDirectory = __DIR__ . '/components';

        $config = $this->getConfig();

        $this->assertEmpty($config->getAnonymousComponentViewFinders());

        $config = $this->getConfig()->addAnonymousComponentPath($componentDirectory);

        $this->assertCount(1, $config->getAnonymousComponentViewFinders());

        /**
         * @var FileSystemViewFinder
         */
        $viewFinder = $config->getAnonymousComponentViewFinders()[0];

        $this->assertInstanceOf(FileSystemViewFinder::class, $viewFinder);
        $this->assertSame($componentDirectory, $viewFinder->basePath);
    }

    /**
     * @depends testAddAnonymousComponentPathRegistersFileSystemViewFinder
     */
    public function testAddAnonymousComponentPathAppendsMultipleFinders(): void
    {
        $paths = [__DIR__, __DIR__ . '/../sub-components'];

        $config = $this->getConfig();

        $this->assertEmpty($config->getAnonymousComponentViewFinders());

        foreach ($paths as $path) {
            $config->addAnonymousComponentPath($path);
        }

        $finders = $config->getAnonymousComponentViewFinders();

        $this->assertCount(count($paths), $finders);
        $this->assertSame($paths, array_column($finders, 'basePath'));
    }

    /**
     * @depends testAddAnonymousComponentPathRegistersFileSystemViewFinder
     */
    public function testAddAnonymousComponentPathAddsNamespaceAsKey(): void
    {
        $namespace = 'katana';
        $componentDirectory = __DIR__ . "/components-{$namespace}";

        $config = $this->getConfig();

        $this->assertEmpty($config->getAnonymousComponentViewFinders());

        $config->addAnonymousComponentPath($componentDirectory, $namespace);

        $finders = $config->getAnonymousComponentViewFinders();

        $this->assertCount(1, $finders);
        $this->assertArrayHasKey($namespace, $finders);
        $this->assertSame($componentDirectory, $finders[$namespace]->basePath);
    }

    /**
     * @depends testAddAnonymousComponentPathAddsNamespaceAsKey
     */
    public function testAddAnonymousComponentPathThrowsExceptionWhenMultipleEntriesAddedForSameNamespace(): void
    {
        $namespace = "katana";

        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(
            Messages::ERROR_MULTIPLE_PATH_FOR_NAMESPACE_NOT_ALLOWED,
            $namespace
        ));


        $config = $this->getConfig();

        $config->addAnonymousComponentPath(__DIR__, $namespace);
        $config->addAnonymousComponentPath(__DIR__ . '/another', $namespace);
    }

    protected function getConfig(string $cachePath = '/'): Config
    {
        return new Config($cachePath);
    }

    protected function getDummyViewFinder(): ViewFinder
    {
        return new class extends ViewFinder {
            public function lastModified(string $name): int
            {
                throw new Exception('Not implemented');
            }

            #[Override]
            public function viewExists(string $name): bool
            {
                throw new Exception('Not implemented');
            }

            #[Override]
            public function getContents(string $name): string
            {
                throw new Exception('Not implemented');
            }

            #[Override]
            public function identifier(): string
            {
                throw new Exception('Not implemented');
            }
        };
    }
}
