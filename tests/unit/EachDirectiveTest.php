<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class EachDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testDirective(): void
    {
        $this->createTemporaryBladeFile(
            '<li>{{ $item }}</li>',
            'list-item',
        );

        $this->assertSame(
            '<li>1</li><li>2</li><li>3</li>',
            $this->renderBlade("@each('list-item', [1, 2, 3], 'item')"),
        );
    }

    public function testWithNameParameter(): void
    {
        $this->createTemporaryBladeFile(
            '<li>{{ $key }}:{{ $value }}</li>',
            'list-item',
        );

        $this->assertSame(
            '<li>0:1</li><li>1:2</li><li>2:3</li>',
            $this->renderBlade("@each('list-item', [1, 2, 3], 'value')"),
        );
    }

    public function testWithEmptyNameParameterDefaultName(): void
    {
        $this->createTemporaryBladeFile(
            '<li>{{ $key }}:{{ $value }}</li>',
            'list-item',
        );

        $this->assertSame(
            '<li>0:1</li><li>1:2</li><li>2:3</li>',
            $this->renderBlade("@each('list-item', [1, 2, 3], '')"),
        );
    }


    public function testWithFallbackView(): void
    {
        $this->createTemporaryBladeFile(
            '<li>{{ $item }}</li>',
            'list-item',
        );

        $this->createTemporaryBladeFile(
            '<li>No list items found.</li>',
            'empty-list',
        );


        $this->assertSame(
            '<li>No list items found.</li>',
            $this->renderBlade("@each('list-item', [], 'item', 'empty-list')"),
        );
    }
}
