<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
class AnonymousIndexComponentTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testIndexComponent(): void
    {
        $this->createComponent('slider.index', "Slider");

        $this->assertSame(
            "Slider",
            $this->renderBlade("<x-slider />")
        );
    }

    public function testComponentFileTakesPriorityOverIndexComponent(): void
    {
        $this->createComponent('slider.index', "Index slider");
        $this->createComponent('slider', "Base slider");


        $this->assertSame(
            "Base slider",
            $this->renderBlade("<x-slider />")
        );
    }

    public function testLoadsComponentFromAdditionalPath(): void
    {
        $additionalDir = $this->blade->viewPath . '/additional-components';

        if (!is_dir($additionalDir) && !mkdir($additionalDir, recursive: true)) {
            throw new Exception('Failed to create directory');
        }

        file_put_contents(
            sprintf('%s/%s', $additionalDir, 'external-view.blade.php'),
            'external view'
        );

        $this->blade->addAnonymousComponentPath($additionalDir);

        $this->assertSame(
            'external view',
            $this->renderBlade('<x-external-view />')
        );
    }
}
