<?php

namespace Tests\Config;

use Blade\Blade;
use Blade\Config;
use Blade\Exceptions\BladeException;
use Blade\Messages;
use Tests\BladeTestCase;

class BladeConfigTest extends BladeTestCase
{
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
}
