<?php

namespace Tests\Config;

use Blade\Blade;
use Blade\Config;
use Blade\Exceptions\BladeException;
use Blade\FileSystemViewFinder;
use Blade\Messages;
use Blade\ViewFinder;
use PHPUnit\Framework\Attributes\Depends;
use Tests\BladeTestCase;
use Tests\VerifiesOutputTrait;

class BladeConfigTest extends BladeTestCase
{
    use VerifiesOutputTrait;

    public function testThrowsExceptionWhenMissingConfig()
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_VIEW_PATH_REQUIRED);

        new Blade();
    }

    public function testThrowsExceptionWhenOnlyViewPathIsPresent()
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_CACHE_PATH_REQUIRED);

        new Blade(__DIR__);
    }

    public function testThrowsExceptionWhenConfigHasFindersAndViewPathIsPresent(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_VIEW_PATH_CONFLICT);

        $viewPath = __DIR__;

        $config = new Config(__DIR__ . '/cache');
        $config->addViewPath($viewPath);

        new Blade($viewPath, config: $config);
    }

    public function testThrowsExceptionWhenConfigHasCachePathAndCachePathParamIsPresent(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_CACHE_PATH_CONFLICT);

        new Blade(
            cachePath: __DIR__ . '/cache',
            config: new Config('/')
        );
    }

    public function testInitialisesFileSystemViewFinderWhenViewPathIsPresentAndConfigHasNoViewFinders(): void
    {
        $viewPath = __DIR__ . '/view-path';

        $blade = new Blade($viewPath, config: new Config('/cache-path'));

        $this->assertCount(1, $blade->config->getViewFinders());

        $viewFinder = $blade->config->getViewFinders()[0];

        $this->assertInstanceOf(FileSystemViewFinder::class, $viewFinder);
        $this->assertSame($viewPath, $viewFinder->basePath);
    }

    public function testThrowsExceptionWhenNoViewFindersAreSet(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(Messages::ERROR_MISSING_DEFAULT_VIEW_FINDER);

        new Blade(config: new Config('/cache-path'));
    }

    public function testDoesNotThrowExceptionWithConfigIsPresentWithAtLeastOneViewFinder(): void
    {
        $config = new Config('/cache-path');
        $config->addViewPath(__DIR__ . '/view-path');

        new Blade(config: $config);

        $this->assertTrue(true);
    }

    public function testInitializesFileSystemViewFinderWhenPathsArePresent(): void
    {
        $this->blade = new Blade($this->getTempDirectory(), $this->getTempDirectory());

        $viewFinder = $this->blade->config->getViewFinders()[0];

        $this->assertInstanceOf(FileSystemViewFinder::class, $viewFinder);
        $this->assertSame($this->getTempDirectory(), $viewFinder->basePath);
    }
}
