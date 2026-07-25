<?php

namespace Tests\Config;

use Blade\Config;
use Blade\FileSystemViewFinder;
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
            public function identity(string $name): string
            {
                throw new Exception('Not implemented');
            }
        };
    }
}
