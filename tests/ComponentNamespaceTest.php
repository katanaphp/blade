<?php

namespace Tests;

use Blade\Exceptions\BladeException;
use Blade\Messages;
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

    public function testRendersComponent(): void
    {
        $namespace = "katana";

        $this->createComponent("alert", "Namespaced component", $namespace);
        $this->blade->config->addAnonymousComponentPath(
            $this->getNamespaceDir($namespace),
            $namespace
        );

        $this->assertSame(
            "Namespaced component",
            $this->renderBlade("<x-katana::alert />"),
            "Failed to render self closing component",
        );

        $this->assertSame(
            "Namespaced component",
            $this->renderBlade("<x-katana::alert></x-katana::alert>"),
            "Failed to render with closing tags",
        );
    }

    public function testNamespacedComponentReceivesAttributesAndSlot(): void
    {
        $this->createComponent(
            'alert',
            '<div class="{{ $type }}">{{ $slot }}</div>',
            'acme',
        );
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir('acme'), 'acme');

        $this->assertSame(
            '<div class="warning">Body</div>',
            $this->renderBlade('<x-acme::alert type="warning">Body</x-acme::alert>'),
        );
    }

    public function testNamespaceResolvesDottedAndIndexComponents(): void
    {
        $this->createComponent('forms.input', 'Namespaced input', 'acme');
        $this->createComponent('slider.index', 'Namespaced slider', 'acme');
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir('acme'), 'acme');

        $this->assertSame('Namespaced input', $this->renderBlade('<x-acme::forms.input />'));
        $this->assertSame('Namespaced slider', $this->renderBlade('<x-acme::slider />'));
    }

    public function testApplicationComponentDoesNotShadowNamespacedComponent(): void
    {
        $this->createComponent('alert', 'Namespaced alert', 'acme');
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir('acme'), 'acme');

        // An application component of the same name must not be picked up for the
        // namespaced tag, and must still resolve for the plain tag.
        $this->createComponent('alert', 'Application alert');

        $this->assertSame('Namespaced alert', $this->renderBlade('<x-acme::alert />'));
        $this->assertSame('Application alert', $this->renderBlade('<x-alert />'));
    }

    public function testNamespacedComponentDoesNotFallBackToApplicationComponents(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_VIEW_NOT_FOUND, "isolated::alert"));

        // "alert" exists only as an application component, never in the namespace.
        $this->createComponent('alert', 'Application alert');
        $this->createComponent('other', 'other', 'isolated');

        $this->blade->addAnonymousComponentPath($this->getNamespaceDir('isolated'), 'isolated');

        $this->renderBlade('<x-isolated::alert />');
    }

    public function testUnknownNamespaceDoesNotResolve(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_VIEW_NOT_FOUND, "nope::alert"));

        $this->createComponent('alert', 'Application alert');
        $this->renderBlade('<x-nope::alert />');
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
