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
            '<div {{ $attributes->filter(fn (string $key, string $value) => str_contains($key, "type")) }}></div>'
        );

        $this->assertSame(
            "<div type='warning' data-type-full='alert-warning'></div>",
            $this->renderBlade('<x-alert type="warning" data-type-full="alert-warning" aria-label="Clicky ti click"/>')
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
