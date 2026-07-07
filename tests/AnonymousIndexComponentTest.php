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
        $additionalDir = $this->getTempDirectory() . '/additional-components';

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

    public function testLoadsComponentFromCustomViewFinder(): void
    {
        $customFinder = new class(['custom-alert' => 'Custom ViewFinder Alert']) extends \Blade\ViewFinder {
            private array $views;

            public function __construct(array $views)
            {
                $this->views = $views;
            }

            public function viewExists(string $name): bool
            {
                return isset($this->views[$name]);
            }

            public function lastModified(string $name): int
            {
                return time();
            }

            public function getContents(string $name): string
            {
                return $this->views[$name] ?? '';
            }
        };

        $this->blade->addAnonymousComponentPath($customFinder);

        $this->assertSame(
            'Custom ViewFinder Alert',
            $this->renderBlade('<x-custom-alert />')
        );
    }
}
