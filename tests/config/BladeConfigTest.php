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

    public function testDoesNotThrowExceptionWithConfigIsPresent()
    {
        new Blade(config: new Config);

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
