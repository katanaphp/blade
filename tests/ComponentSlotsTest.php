<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
class ComponentSlotsTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testDefaultSlot()
    {
        $this->createComponent('card', "<div class='card'>{{ \$slot }}</div>");

        $this->assertEquals(
            '<div class=\'card\'>Hello, World!</div>',
            $this->renderBlade('<x-card>Hello, World!</x-card>')
        );
    }

    public function testNamedSlot()
    {
        $this->createComponent(
            'card',
            "<div class='card'> {{ \$header }} {{ \$slot }} </div>"
        );


        $blade = <<<'BLADE'
        <x-card>
            <x-slot name="header">
                Maria's Guide to the Galaxy
            </x-slot>
            <p>Don't Panic</p>
        </x-card>
        BLADE;


        $this->assertEquals(
            "<div class='card'> Maria's Guide to the Galaxy <p>Don't Panic</p> </div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testMultipleNamedSlot(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'> {{ \$header }} {{ \$slot }} {{ \$footer }} </div>"
        );

        $blade = <<<'BLADE'
        <x-card>
            <x-slot name="header">
                Maria's Guide to the Galaxy
            </x-slot>

            <x-slot name="footer">
                <footer>
                    That's all, folks!
                </footer>
            </x-slot>

            <article>
                <p>Don't Panic</p>
            </article>
        </x-card>
        BLADE;

        $this->assertEquals(
            "<div class='card'> Maria's Guide to the Galaxy " .
                "<article> <p>Don't Panic</p> </article> " .
                "<footer> That's all, folks! </footer> " .
                "</div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testEmptyMethod(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@unless(\$slot->isEmpty()) {{ \$slot }} with some additional content @endunless</div>"
        );

        $this->assertEquals(
            "<div class='card'></div>",
            $this->renderBlade('<x-card></x-card>')
        );

        $this->assertEquals(
            "<div class='card'> I am slotted with some additional content </div>",
            $this->renderBlade('<x-card>I am slotted</x-card>')
        );
    }

    public function testHasActualContentWithEmptySlot(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $this->assertEquals(
            "<div class='card'></div>",
            $this->renderBlade('<x-card></x-card>')
        );
    }

    public function testHasActualContentWithWhitespaceOnly(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $this->assertEquals(
            "<div class='card'></div>",
            $this->renderBlade('<x-card>   </x-card>')
        );
    }

    public function testHasActualContentWithSingleLineComment(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $this->assertEquals(
            "<div class='card'></div>",
            $this->renderBlade('<x-card><!-- This is a comment --></x-card>')
        );
    }

    public function testHasActualContentWithMultiLineComment(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $blade = <<<'BLADE'
        <x-card>
            <!--
                This is a
                multi-line comment
            -->
        </x-card>
        BLADE;

        $this->assertEquals(
            "<div class='card'></div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testHasActualContentWithActualContentAndComments(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $blade = <<<'BLADE'
        <x-card>
            <!-- Comment -->
            Hello World
            <!-- Another comment -->
        </x-card>
        BLADE;

        $this->assertEquals(
            "<div class='card'> Content exists </div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testHasActualContentWithOnlyActualContent(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>@if(\$slot->hasActualContent()) Content exists @endif</div>"
        );

        $this->assertEquals(
            "<div class='card'> Content exists </div>",
            $this->renderBlade('<x-card>Hello World</x-card>')
        );
    }

    public function testSlotAttributesIsInstanceOfAttributes(): void
    {
        $this->createComponent(
            'card',
            "{{ \$header->attributes instanceof Blade\\Attributes ? 'yes' : 'no' }}"
        );

        $blade = <<<'BLADE'
        <x-card>
            <x-slot name="header" data-test="value">
                Content
            </x-slot>
        </x-card>
        BLADE;

        $this->assertEquals(
            "yes",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testSlotAttributesWithComponent(): void
    {
        $this->createComponent(
            'card',
            "<div {{ \$attributes}}>{{ \$header->attributes instanceof Blade\\Attributes ? 'yes' : 'no' }}</div>"
        );

        $blade = <<<'BLADE'
        <x-card class="shadow">
            <x-slot name="header" data-test="value">
                Content
            </x-slot>
        </x-card>
        BLADE;

        $this->assertEquals(
            "<div class='shadow'>yes</div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }

    public function testSlotAttributesMultiple(): void
    {
        $this->createComponent(
            'card',
            "<div class='card'>{{ \$content->attributes->get('data-id') }} | {{ \$content->attributes->get('data-type') }} - {{ \$content }}</div>"
        );

        $blade = <<<'BLADE'
        <x-card>
            <x-slot name="content" data-id="content-1" data-type="main">
                Slot Content
            </x-slot>
        </x-card>
        BLADE;

        $this->assertEquals(
            "<div class='card'>content-1 | main - Slot Content </div>",
            $this->removeIndentation($this->renderBlade($blade))
        );
    }
}
