<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
class ComponentTagsTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testSelfClosingTagOutput()
    {
        $this->createComponent('alert', '<div>Hello, World!</div>');

        $this->assertSame(
            "<div>Hello, World!</div>",
            $this->renderBlade("<x-alert />")
        );
    }

    public function testSelfClosingComponentWithoutSpace()
    {
        $this->createComponent('alert', '<div>Hello, World!</div>');

        $this->assertSame(
            "<div>Hello, World!</div>",
            $this->renderBlade("<x-alert/>")
        );
    }

    public function testComponentNameWithHypen()
    {
        $this->createComponent('alert-info', '<div>Hello, World! {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert-info name="Taylor" />')
        );
    }

    public function testComponentNameWithDirectoryDot()
    {
        $this->createComponent('alerts.info', '<div>Hello, World! {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alerts.info name="Taylor" />')
        );
    }

    public function testWithBooleanAttribute()
    {
        $this->createComponent('alert', '<div>Hello, World! {{ $show }}</div>');

        $this->assertSame(
            "<div>Hello, World! 1</div>",
            $this->renderBlade('<x-alert show />')
        );
    }

    public function testWithAttribute()
    {
        $this->createComponent('alert', '<div>Hello, World! {{ $name }}</div>');

        $this->assertSame(
            "<div>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert name="Taylor" />')
        );
    }

    public function testWithSnakeCaseAttribute()
    {
        $this->createComponent(
            'alert',
            '<div>Hello, World! {{ $firstName }} {{ $lastName }}</div>'
        );

        $this->assertSame(
            "<div>Hello, World! Maria Jose</div>",
            $this->renderBlade('<x-alert first-name="Maria" last-name="Jose" />')
        );
    }

    public function testAttributeOutput(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes }}>Hello, World! {{ $name }}</div>'
        );

        $this->assertSame(
            "<div name='Taylor'>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert name="Taylor" />')
        );
    }

    public function testEscapesAttributeOutput(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes }}>Hello, World!</div>'
        );

        $this->assertSame(
            "<div name='&lt;script&gt;alert(&#039;Hello, World!&#039;)&lt;/script&gt;'>Hello, World!</div>",
            $this->renderBlade('<x-alert name="<script>alert(\'Hello, World!\')</script>" />')
        );
    }

    public function testMergeAttributes(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->merge([ "hello" => "kitty" ]) }}>Hello, World!</div>'
        );

        $this->assertSame(
            "<div class='alert' name='Taylor' hello='kitty'>Hello, World!</div>",
            $this->renderBlade('<x-alert class="alert" name="Taylor" />')
        );
    }

    public function testExceptAttribute(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->except("name") }}>Hello, World!</div>'
        );

        $this->assertSame(
            "<div class='alert'>Hello, World!</div>",
            $this->renderBlade('<x-alert class="alert" name="Taylor"/>')
        );
    }

    public function testExceptAttributeWithArray(): void
    {
        $this->createComponent(
            'alert',
            '<div{{ $attributes->except(["name", "class"]) }}>Hello, World! {{ $name }}</div>'
        );

        $this->assertSame(
            "<div>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert class="alert" name="Taylor"/>')
        );
    }

    public function testPropsSetDefaults(): void
    {
        $this->createComponent(
            'alert',
            '@props(["name" => "Taylor"])' .
                '<div>Hello, World! {{ $name }}</div>'
        );

        $this->assertSame(
            "<div>Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert />')
        );
    }

    public function testPropsIndexedKeySetsValueToNull(): void
    {
        $this->createComponent(
            'alert',
            '@props(["name"])@if(is_null($name)){{ "I am null" }}@endif'
        );

        $this->assertSame(
            "I am null",
            $this->renderBlade('<x-alert />')
        );
    }

    public function testPropsPreventOutputInAttributes(): void
    {
        $this->createComponent(
            'alert',
            '@props(["name", "nativeLanguage"])<div {{ $attributes }}>Hello, World! {{ $name }}</div>'
        );

        $this->assertSame(
            "<div >Hello, World! Taylor</div>",
            $this->renderBlade('<x-alert name="Taylor" native-language="hi" />')
        );
    }

    public function testClosingComponents(): void
    {
        $this->createComponent(
            'alert',
            '<div></div>'
        );

        $this->assertSame(
            "<div></div>",
            $this->renderBlade('<x-alert></x-alert>')
        );
    }

    public function testClosingComponentsWithHyphen(): void
    {
        $this->createComponent(
            'alert-info',
            '<div></div>'
        );

        $this->assertSame(
            "<div></div>",
            $this->renderBlade('<x-alert-info></x-alert-info>')
        );
    }

    public function testClosingComponentsWithDirectoryDot(): void
    {
        $this->createComponent(
            'alerts.info',
            '<div></div>'
        );

        $this->assertSame(
            "<div></div>",
            $this->renderBlade('<x-alerts.info></x-alerts.info>')
        );
    }

    public function testClosingComponentsWithAttributes(): void
    {
        $this->createComponent(
            'alert',
            '<div>{{ $name }}</div>'
        );

        $this->assertSame(
            "<div>Maria</div>",
            $this->renderBlade('<x-alert name="Maria"></x-alert>')
        );
    }

    public function testClosingComponentsWithAttributesAndSlot(): void
    {
        $this->createComponent(
            'alert',
            '<div>{{ $name }} {{ $slot }}</div>'
        );

        $this->assertSame(
            "<div>Maria Hello, World!</div>",
            $this->renderBlade('<x-alert name="Maria">Hello, World!</x-alert>')
        );
    }

    protected function testSupportsAttributesWithSingleQuotes()
    {
        $this->createComponent(
            'component',
            '<div>{{ $name }} is {{ $age }} old</div>'
        );

        $this->assertEquals(
            $this->renderBlade('<x-component name="John" age=\'30\' />'),
            "<div>John is 30 years old</div>"
        );
    }

    protected function testComponentSupportsAttributesWithSingleQuotes()
    {
        $this->createComponent(
            'component',
            '<div>{{ $name }} is {{ $age }} old</div>'
        );

        $this->assertEquals(
            $this->renderBlade('<x-component name="John" age=\'30\'></x-component>'),
            "<div>John is 30 years old</div>"
        );
    }

    public function testAttributesGetMethod()
    {
        $this->createComponent(
            'component',
            '{{ $attributes->get("last") }}'
        );

        $this->assertEquals(
            $this->renderBlade('<x-component first-name="Dave" last="The Octopus" />'),
            'The Octopus'
        );
    }

    public function testAttributeWithMultipleWords()
    {
        $this->createComponent(
            'component',
            '{{ $attributes->get("first-name") }}'
        );

        $this->assertEquals(
            $this->renderBlade('<x-component first-name="Dave"/>'),
            'Dave'
        );
    }

    public function testMultipleNonValueAttributes(): void
    {
        $this->createComponent(
            'component',
            '@if($hasCat) Has Cat @endif' .
                '@if($hasDog) Has Dog @endif'
        );

        $this->assertSame(
            "Has Cat Has Dog",
            /**
             * Remove extra spaces as template
             * will have extra spaces due to formatting
             */
            preg_replace(
                '/\s+/',
                " ",
                trim($this->renderBlade('<x-component has-cat has-dog />'))
            )
        );
    }

    public function testEmptyValueAttribute(): void
    {
        $this->createComponent(
            'component',
            '<p>Component Content - @empty($emptyAttribute) Empty Attribute @endempty</p>'
        );

        $cases = [
            '<x-component empty-attribute=""/>',
            "<x-component empty-attribute=''/>",

            '<x-component empty-attribute=""></x-component>',
            "<x-component empty-attribute=''></x-component>",
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                "<p>Component Content - Empty Attribute </p>",
                $this->removeIndentation(
                    $this->renderBlade($case)
                ),
                "Failed for case: $case"
            );
        }
    }

    public function testEscapesAttributesWithDoubleColon()
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes }}></div>'
        );

        $this->assertSame(
            "<div :class='hello'></div>",
            $this->renderBlade('<x-alert ::class="hello" />')
        );
    }

    public function testShortAttributeSyntax()
    {
        $this->createComponent(
            'alert',
            '@props(["type"]) {{ $type }}'
        );

        $this->assertSame(
            'warning',
            $this->removeIndentation($this->renderBlade('<x-alert :$type />', ['type' => 'warning']))
        );
    }

    public function testShortMultiWordAttribute()
    {
        $this->createComponent(
            'alert',
            '@props(["messageLength"]){{ $messageLength }}'
        );

        $this->assertSame(
            'Long message',
            $this->renderBlade('<x-alert :$messageLength />', ['messageLength' => 'Long message'])
        );
    }
}
