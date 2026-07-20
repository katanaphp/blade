<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
/**
 * @group component
 */
class ComponentNamespaceTest extends TestCase
{
    use VerifiesOutputTrait;

    /**
     * Creates a component inside a directory dedicated to $namespace and returns
     * that directory. Created files are tracked so they are removed in tearDown
     * and cannot leak into another test.
     */
    private function createNamespacedComponent(string $namespace, string $name, string $template): string
    {
        $directory = sprintf('%s/%s-components', $this->getTempDirectory(), $namespace);

        $file = sprintf('%s/%s.blade.php', $directory, str_replace('.', '/', $name));
        $parent = pathinfo($file, PATHINFO_DIRNAME);

        if (!is_dir($parent) && !mkdir($parent, recursive: true)) {
            throw new Exception('Failed to create directory');
        }

        file_put_contents($file, $template);
        $this->createdFiles[] = $file;

        return $directory;
    }

    /**
     * Renders a template that is expected not to resolve, returning null instead.
     * An unresolved component aborts mid-render, so any output buffer it opened is
     * unwound here to keep the test from leaking buffers.
     */
    private function renderUnresolvable(string $template): ?string
    {
        $level = ob_get_level();

        try {
            return $this->renderBlade($template);
        } catch (\Throwable $e) {
            return null;
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }
    }

    public function testRendersComponentFromNamespace(): void
    {
        $directory = $this->createNamespacedComponent('acme', 'alert', 'Namespaced alert');
        $this->blade->addAnonymousComponentPath($directory, 'acme');

        $this->assertSame(
            'Namespaced alert',
            $this->renderBlade('<x-acme::alert />'),
        );
    }

    public function testNamespacedComponentReceivesAttributesAndSlot(): void
    {
        $directory = $this->createNamespacedComponent(
            'acme',
            'alert',
            '<div class="{{ $type }}">{{ $slot }}</div>',
        );
        $this->blade->addAnonymousComponentPath($directory, 'acme');

        $this->assertSame(
            '<div class="warning">Body</div>',
            $this->renderBlade('<x-acme::alert type="warning">Body</x-acme::alert>'),
        );
    }

    public function testNamespaceResolvesDottedAndIndexComponents(): void
    {
        $directory = $this->createNamespacedComponent('acme', 'forms.input', 'Namespaced input');
        $this->createNamespacedComponent('acme', 'slider.index', 'Namespaced slider');
        $this->blade->addAnonymousComponentPath($directory, 'acme');

        $this->assertSame('Namespaced input', $this->renderBlade('<x-acme::forms.input />'));
        $this->assertSame('Namespaced slider', $this->renderBlade('<x-acme::slider />'));
    }

    public function testApplicationComponentDoesNotShadowNamespacedComponent(): void
    {
        $directory = $this->createNamespacedComponent('acme', 'alert', 'Namespaced alert');
        $this->blade->addAnonymousComponentPath($directory, 'acme');

        // An application component of the same name must not be picked up for the
        // namespaced tag, and must still resolve for the plain tag.
        $this->createComponent('alert', 'Application alert');

        $this->assertSame('Namespaced alert', $this->renderBlade('<x-acme::alert />'));
        $this->assertSame('Application alert', $this->renderBlade('<x-alert />'));
    }

    public function testNamespacedComponentDoesNotFallBackToApplicationComponents(): void
    {
        // "alert" exists only as an application component, never in the namespace.
        $this->createComponent('alert', 'Application alert');
        $this->blade->addAnonymousComponentPath(
            $this->createNamespacedComponent('isolated', 'other', 'other'),
            'isolated',
        );

        $this->assertNotSame(
            'Application alert',
            $this->renderUnresolvable('<x-isolated::alert />'),
        );
    }

    public function testUnknownNamespaceDoesNotResolve(): void
    {
        $this->createComponent('alert', 'Application alert');

        $this->assertNotSame(
            'Application alert',
            $this->renderUnresolvable('<x-nope::alert />'),
        );
    }

    public function testNamespaceAcceptsACustomViewFinder(): void
    {
        $customFinder = new class(['alert' => 'Finder alert']) extends \Blade\ViewFinder {
            public function __construct(private array $views) {}

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

        $this->blade->addAnonymousComponentViewFinder($customFinder, 'acme');

        $this->assertSame('Finder alert', $this->renderBlade('<x-acme::alert />'));
    }
}
