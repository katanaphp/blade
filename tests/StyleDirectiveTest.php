<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class StyleDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testSimpleClassDirective()
    {
        $this->assertSame(
            'style="display:none; color:red;"',
            $this->renderBlade('@style(["display:none", "color:red;",])')
        );
    }

    public function testConditionalStyles()
    {
        $this->assertSame(
            'style="display:none;"',
            $this->renderBlade(
                '@style(["display:none" => $isMobile, "color: red" => !$isMobile])',
                ['isMobile' => true]
            )
        );

        $this->assertSame(
            'style="color: red;"',
            $this->renderBlade(
                '@style(["display:none" => $isMobile, "color: red" => !$isMobile])',
                ['isMobile' => false]
            )
        );
    }

    public function testEscapesSingleQuotes(): void
    {
        $this->assertSame(
            'style="background: url(&#039;https://example.com/image.png&#039;);"',

            $this->renderBlade(
                '@style($styles)',
                ['styles' => ["background: url('https://example.com/image.png')"]]
            )
        );
    }

    public function testEscapesDoubleQuotes(): void
    {
        $this->assertSame(
            'style="background: url(&quot;https://example.com/image.png&quot;);"',

            $this->renderBlade(
                '@style($styles)',
                ['styles' => ['background: url("https://example.com/image.png")']]
            )
        );
    }

    public function testEscapesTagBreakout(): void
    {
        $this->assertSame(
            'style="&quot;&gt; &lt;script&gt;alert(1)&lt;/script&gt;;"',

            $this->renderBlade(
                '@style($styles)',
                ['styles' => ['"> <script>alert(1)</script>']]
            )
        );
    }

    public function testEscapesConditionalKeyValues(): void
    {
        $this->assertSame(
            'style="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;;"',

            $this->renderBlade(
                '@style($styles)',
                ['styles' => ['"><img src=x onerror=alert(1)>' => true]]
            )
        );
    }
}
