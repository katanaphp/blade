<?php

namespace Tests;

use Blade\ComponentRenderer;
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

    public function testThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(
            Messages::ERROR_VIEW_NOT_FOUND,
            "katana::button"
        ));

        $this->renderBlade('<x-katana::button>Hello</x-katana::button>');
    }


    public function testDoesNotRenderInvalidSyntax(): void
    {
        $this->assertSame("<x-::button />", $this->renderBlade('<x-::button />'));
        $this->assertSame("<x-:button />", $this->renderBlade('<x-:button />'));
    }


    public function testStandardComponentDoesNotUseEmptyNamespaceSeparator(): void
    {
        $this->createComponent('button', 'Standard Button');

        try {
            $this->assertSame('Standard Button', $this->renderBlade('<x-button />'));
        } catch (BladeException $e) {
            $this->fail('Standard component rendering failed: ' . $e->getMessage());
        }
    }


    public function testGlobalComponentResolutionConsistency(): void
    {
        $this->createComponent('alert.alert', 'Global Shadow Alert');
        $this->assertSame('Global Shadow Alert', $this->renderBlade('<x-alert />'));
    }

    public function testNumericComponentName(): void
    {
        $namespace = 'ui';
        $this->blade->config->addAnonymousComponentPath($this->getNamespaceDir($namespace), $namespace);
        $this->createComponent('123', 'Numeric Component', $namespace);

        $this->assertSame('Numeric Component', $this->renderBlade('<x-ui::123 />'));
    }

    public function testNamespaceAndNameCollision(): void
    {
        $name = 'ui';
        $this->blade->config->addAnonymousComponentPath($this->getNamespaceDir($name), $name);
        $this->createComponent($name, 'Collision Component', $name);

        $this->assertSame('Collision Component', $this->renderBlade("<x-{$name}::{$name} />"));
    }

    public function testAnonymousComponentsAreLoadedInOrderOfRegistration(): void
    {
        $path1 = $this->getNamespaceDir('global1');
        $path2 = $this->getNamespaceDir('global2');

        $this->blade->config->addAnonymousComponentPath($path1);
        $this->blade->config->addAnonymousComponentPath($path2);

        $this->createComponent('button', 'Button 1', 'global1');
        $this->createComponent('button', 'Button 2', 'global2');

        $this->assertSame('Button 1', $this->renderBlade('<x-button />'));
    }

    public function testDoesNotReferenceNamespaceLessComponentWithArrayIndexes(): void
    {
        $this->expectException(BladeException::class);
        $this->expectExceptionMessage(sprintf(Messages::ERROR_VIEW_NOT_FOUND, '0::button'));

        $this->assertEmpty($this->blade->config->getAnonymousComponentViewFinders());

        $this->blade->config->addAnonymousComponentPath(
            $this->getNamespaceDir('katana'),
            'katana'
        );

        $finders = $this->blade->config->getAnonymousComponentViewFinders();

        $this->assertCount(1, $finders);
        $this->assertSame('katana', $finders[0]['namespace']);

        $this->createComponent('button', 'Katana Button', 'katana');

        $this->renderBlade('<x-0::button />');
    }

    public function testSupportNumericNamespaceWhenExplicitlyRegistered(): void
    {
        $this->assertEmpty($this->blade->config->getAnonymousComponentViewFinders());

        $this->blade->config->addAnonymousComponentPath(
            $this->getNamespaceDir('katana'),
            'katana'
        );

        $this->createComponent('button', 'Katana button', 'katana');

        try {
            $this->renderBlade("<x-0::button />");
            $this->fail('Array index being used as namespace');
        } catch (BladeException $e) {
        }

        $this->blade->config->addAnonymousComponentPath(
            $this->getNamespaceDir('0'),
            '0'
        );

        $this->createComponent('button', 'Numeric button', '0');
        $this->assertSame('Numeric button', $this->renderBlade('<x-0::button/>'));
    }

    /**
     * Determines if component is rendered using same program
     * flow this will ensure that, slot and attributes continue
     * to work as regular component, but this test does not ensure
     * that compiler pipline is the same.
     *
     */
    public function testUsesSameComponentRendererPipeline(): void
    {
        $renderer = $this->getMockBuilder(ComponentRenderer::class)
            ->setConstructorArgs(['blade' => $this->blade])
            ->getMock();

        $this->blade->componentRenderer = $renderer;

        $invocations = $this->exactly(2);

        $renderer->expects($invocations)
            ->method('prepare')
            ->willReturnCallback(function (...$arguments) use ($invocations) {

                $attributes = [];
                $directiveCompat = false;

                if ($invocations->getInvocationCount() === 1) {
                    $namespace = 'katana';
                    $component = 'alert';

                    $this->assertSame(
                        [$namespace, $component, $attributes, $directiveCompat],
                        $arguments
                    );
                } elseif ($invocations->getInvocationCount() === 2) {
                    $namespace = '';
                    $component = 'homepage';

                    $this->assertSame(
                        [$namespace, $component, $attributes, $directiveCompat],
                        $arguments
                    );
                }
            });

        // Try once with namespace
        $this->renderBlade('<x-katana::alert/>');

        // Try again with regular component
        $this->renderBlade('<x-homepage/>');
    }

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

    public function testRendersIndexComponents(): void
    {
        $namespace = 'katana';

        $this->blade->config->addAnonymousComponentPath(
            $this->getNamespaceDir($namespace),
            $namespace
        );

        $this->createComponent('accordion.body', 'accordion body', $namespace);

        $cases = [
            [
                'component' => 'accordion',
                'template' => 'accordion',
                'message' => 'Could not resolve the component blade file as same name in directory',
            ],
            [
                'component' => 'accordion.accordion',
                'template' => 'accordion.accordion',
                'message' => 'Could not resolve the component blade file as same name in directory',
            ],
            [
                'component' => 'accordion.index',
                'template' => 'accordion',
                'message' => 'Could not resolve the component blade file as same name in directory',
            ],
        ];


        foreach ($cases as $case) {
            $this->createComponent(
                $case['component'],
                $case['template'],
                $namespace
            );

            $this->assertSame(
                $case['template'],
                $this->renderBlade("<x-katana::accordion />"),
                $case['message'],
            );

            $this->cleanUpGeneratedFiles();
        }
    }

    public function testSupportsNumericNamespaces(): void
    {
        $namespace = 'v2';

        $this->createComponent('alert', 'V2 alert', $namespace);
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir($namespace), $namespace);

        $this->assertSame(
            'V2 alert',
            $this->renderBlade('<x-v2::alert />'),
        );

        $namespace = '123';

        $this->createComponent('alert', 'Numeric namespace alert', $namespace);
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir($namespace), $namespace);

        $this->assertSame(
            'Numeric namespace alert',
            $this->renderBlade('<x-123::alert />'),
        );
    }

    public function testSupportsUppercaseInNamespaces(): void
    {
        $namespace = 'Acme';

        $this->createComponent('alert', 'Uppercase namespace alert', $namespace);
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir($namespace), $namespace);

        $this->assertSame(
            'Uppercase namespace alert',
            $this->renderBlade('<x-Acme::alert />'),
        );
    }

    public function testSupportsHypnatedNamespace(): void
    {
        $namespace = 'my-lib';

        $this->createComponent('alert', 'Hyphenated namespace alert', $namespace);
        $this->blade->addAnonymousComponentPath($this->getNamespaceDir($namespace), $namespace);

        $this->assertSame(
            'Hyphenated namespace alert',
            $this->renderBlade('<x-my-lib::alert />'),
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

            public function identifier(): string
            {
                return 'acme-identifier';
            }
        };

        $this->blade->addAnonymousComponentViewFinder($customFinder, 'acme');

        $this->assertSame('Finder alert', $this->renderBlade('<x-acme::alert />'));
    }
}
