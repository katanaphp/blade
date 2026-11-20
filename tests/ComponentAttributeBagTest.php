<?php

namespace Tests;

use Blade\Attributes;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('component')]
class ComponentAttributeBagTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testClassesWithMerge(): void
    {
        $this->createComponent('alert', '<div {{ $attributes->merge(["class" => "alert"]) }}></div>');

        $this->assertSame(
            '<div class=\'big alert\'></div>',
            $this->renderBlade('<x-alert class="big"/>')
        );
    }

    public function testClassMethodWithArray(): void
    {
        $this->createComponent('alert', '<div {{ $attributes->class(["alert"]) }}></div>');

        $this->assertSame(
            '<div class=\'alert\'></div>',
            $this->renderBlade('<x-alert />')
        );
    }

    public function testClassMethodWithConditionalClasses(): void
    {
        $this->createComponent('alert', '<div {{ $attributes->class(["alert", "hidden" => true]) }}></div>');

        $this->assertSame(
            '<div class=\'alert hidden\'></div>',
            $this->renderBlade('<x-alert />')
        );

        $this->createComponent('alert', '<div {{ $attributes->class(["alert", "hidden" => false]) }}></div>');

        $this->assertSame(
            '<div class=\'alert\'></div>',
            $this->renderBlade('<x-alert />')
        );
    }

    public function testClassMethodWithString(): void
    {
        $this->createComponent('alert', '<div {{ $attributes->class("alert") }}></div>');

        $this->assertSame(
            '<div class=\'alert\'></div>',
            $this->renderBlade('<x-alert />')
        );
    }

    public function testHasMethod(): void
    {
        $this->createComponent(
            'alert',
            '@if($attributes->has("type")){{ "Attribute Found" }}@endif'
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert type="button"/>')
        );
    }

    public function testHasMethodMultiWordAttribute(): void
    {
        $this->createComponent(
            'alert',
            '@if($attributes->has("aria-label")){{ "Attribute Found" }}@endif'
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert aria-label="Clicky ti click"/>')
        );
    }

    public function testHasMethodWithBooleanValue(): void
    {
        $this->createComponent(
            'alert',
            '@if($attributes->has("disabled")){{ "Attribute Found" }}@endif'
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert disabled/>')
        );

        $this->assertSame(
            '',
            $this->renderBlade('<x-alert/>')
        );
    }

    public function testHasWithArray(): void
    {
        $this->createComponent(
            'alert',
            '@if($attributes->has(["type", "aria-label"])){{ "Attribute Found" }}@endif'
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert type="button" aria-label/>')
        );
    }

    public function testHasAnyWithArray(): void
    {
        $this->createComponent(
            'alert',
            '@if($attributes->hasAny(["type", "aria-label"])){{ "Attribute Found" }}@endif'
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert type="button"/>')
        );

        $this->assertSame(
            'Attribute Found',
            $this->renderBlade('<x-alert aria-label="Clicky ti click"/>')
        );


        $this->assertSame(
            '',
            $this->renderBlade('<x-alert/>')
        );
    }

    public function testFilterMethod()
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->filter(fn (string $value, string $key) => str_contains($key, "type")) }}></div>'
        );

        $this->assertSame(
            "<div type='warning' data-type-full='alert-warning'></div>",
            $this->renderBlade('<x-alert type="warning" data-type-full="alert-warning" aria-label="Clicky ti click"/>')
        );
    }

    public function testFilterMethodPassesValueAsFirstArgument(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->filter(fn (string $value) => $value === "warning") }}></div>'
        );

        $this->assertSame(
            "<div type='warning'></div>",
            $this->renderBlade('<x-alert type="warning" data-type-full="alert-warning" aria-label="warning sign"/>')
        );
    }

    public function testFilterMethodPassesKeyAsSecondArgument(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->filter(fn (string $value, string $key) => $key === "type" && $value === "warning") }}></div>'
        );

        $this->assertSame(
            "<div type='warning'></div>",
            $this->renderBlade('<x-alert type="warning" warning="type"/>')
        );
    }

    public function testFilterDoesntMutateOriginalAttributeBag(): void
    {
        $this->createComponent(
            'alert',
            '@php $attributes->filter(fn (string $value, string $key) => $key === "type"); @endphp' .
                '<div {{ $attributes }}></div>'
        );

        $this->assertSame(
            "<div type='warning' id='notice'></div>",
            $this->renderBlade('<x-alert type="warning" id="notice"/>')
        );
    }

    public function testOnlyMethodWithArray(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->only(["class", "id"]) }}></div>'
        );

        $this->assertSame(
            "<div class='big' id='notice'></div>",
            $this->renderBlade('<x-alert type="warning" class="big" id="notice" aria-label="Clicky ti click"/>')
        );
    }

    public function testOnlyMethodWithString(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->only("class") }}></div>'
        );

        $this->assertSame(
            "<div class='big'></div>",
            $this->renderBlade('<x-alert type="warning" class="big" id="notice"/>')
        );
    }

    public function testOnlyMethodKeepsOrderInWhichAttributesWerePassed(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->only(["id", "class"]) }}></div>'
        );

        $this->assertSame(
            "<div class='big' id='notice'></div>",
            $this->renderBlade('<x-alert class="big" type="warning" id="notice"/>')
        );
    }

    public function testOnlyMethodIgnoresAttributesThatWereNotPassed(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->only(["class", "id"]) }}></div>'
        );

        $this->assertSame(
            "<div class='big'></div>",
            $this->renderBlade('<x-alert class="big" type="warning"/>')
        );

        $this->assertSame(
            "<div ></div>",
            $this->renderBlade('<x-alert type="warning"/>')
        );
    }

    public function testOnlyMethodWithMultiWordAttribute(): void
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->only("aria-label") }}></div>'
        );

        $this->assertSame(
            "<div aria-label='Clicky ti click'></div>",
            $this->renderBlade('<x-alert type="warning" aria-label="Clicky ti click"/>')
        );
    }

    public function testOnlyAndExceptSplitAttributesBetweenElements(): void
    {
        $this->createComponent(
            'field',
            '<label {{ $attributes->only("class") }}><input {{ $attributes->except("class") }}></label>'
        );

        $this->assertSame(
            "<label class='wide'><input type='email' name='email'></label>",
            $this->renderBlade('<x-field class="wide" type="email" name="email"/>')
        );
    }

    public function testOnlyDoesntMutateOriginalAttributeBag(): void
    {
        $this->createComponent(
            'alert',
            '@php $attributes->only("class"); @endphp<div {{ $attributes }}></div>'
        );

        $this->assertSame(
            "<div class='big' id='notice'></div>",
            $this->renderBlade('<x-alert class="big" id="notice"/>')
        );
    }

    public function testWhereStartsWithMethod()
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->whereStartsWith("type") }}></div>'
        );

        $this->assertSame(
            "<div type='warning'></div>",
            $this->renderBlade('<x-alert type="warning" data-type-full="alert-warning" aria-label="Clicky ti click"/>')
        );
    }

    public function testDoesntWhereStartWithMethod()
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->whereDoesntStartWith("type") }}></div>'
        );

        $this->assertSame(
            "<div color='alert-warning' label='Clicky ti click'></div>",
            $this->renderBlade('<x-alert type="warning" color="alert-warning" label="Clicky ti click"/>')
        );
    }

    public function testFirstMethod()
    {
        $this->createComponent(
            'alert',
            '<div {{ $attributes->first() }}></div>'
        );

        $this->assertSame(
            "<div type='warning'></div>",
            $this->renderBlade('<x-alert type="warning" color="alert-warning" label="Clicky ti click"/>')
        );
    }

    public function testPrependsMethod(): void
    {
        $this->createComponent(
            "alert",
            '<div {{ $attributes->merge( ["aria-label" => $attributes->prepends("hello ")] ) }}></div>'
        );

        $this->assertSame(
            "<div aria-label='hello Clicky ti click'></div>",
            $this->renderBlade('<x-alert aria-label="Clicky ti click"/>')
        );
    }

    public function testMultiWordAttributes(): void
    {
        $this->createComponent(
            "alert",
            '<div {{ $attributes }}></div>'
        );

        $this->assertSame(
            "<div aria-label='Clicky ti click'></div>",
            $this->renderBlade('<x-alert aria-label="Clicky ti click"/>')
        );
    }


    public function testMergeDoesntMutateOriginalAttributeBag(): void
    {
        $original = ['data-first-name' => 'Maria'];

        $attributes = new Attributes($original);
        $merged = $attributes->merge(['data-last-name' => 'Jose']);

        $this->assertInstanceOf(Attributes::class, $merged);
        $this->assertNotSame($attributes, $merged);

        $this->assertSame($original, $attributes->toArray());
    }

    public function testClassDoesntMutateOriginalAttributeBag(): void
    {
        $original = ['class' => 'alert'];

        $attributes = new Attributes($original);
        $classed = $attributes->class('hidden');

        $this->assertInstanceOf(Attributes::class, $classed);
        $this->assertNotSame($attributes, $classed);

        $this->assertSame($original, $attributes->toArray());
    }

    public function testMergeAttributesGetsReplaced(): void
    {
        $this->createComponent('alert', '<div {{ $attributes->merge(["role" => "alert"]) }}></div>');

        $this->assertSame("<div role='alert'></div>", $this->renderBlade('<x-alert/>'));
        $this->assertSame("<div role='group'></div>", $this->renderBlade('<x-alert role="group"/>'));
    }

    public function testMergeUsesDefaultValueWhenPropExistsAndWhenAttributeIsPassed(): void
    {
        $this->createComponent(
            'button',
            '@props(["type"=>"button"])' .
                '<button {{ $attributes->merge(["type" => "submit"]) }}></button>'
        );

        $this->assertSame(
            "<button type='submit'></button>",
            $this->renderBlade("<x-button/>")
        );

        $this->assertSame(
            "<button type='submit'></button>",
            $this->renderBlade("<x-button type='reset' />")
        );
    }

    public function testOnlyRendersStringBoolNumbers(): void
    {
        $cases = [
            [
                'value' => true,
                'expected' => "<div aria-label='1'></div>",
            ],
            [
                'value' => false,
                'expected' => "<div aria-label=''></div>",
            ],
            [
                'value' => 1,
                'expected' => "<div aria-label='1'></div>",
            ],
            [
                'value' => 0,
                'expected' => "<div aria-label='0'></div>",
            ],
            [
                'value' => ['#data', 'selector'],
                'expected' => "<div ></div>",
            ],
            [
                'value' => (new class
                {
                }),
                'expected' => "<div ></div>",
            ]
        ];
        $this->createComponent(
            "alert",
            '<div {{ $attributes }}></div>'
        );

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade(
                    '<x-alert :aria-label="$value"/>',
                    [
                        'value' => $case['value']
                    ]
                )
            );
        }

        // $this->ex
    }
}
