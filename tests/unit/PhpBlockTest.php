<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class PhpBlockTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testBlock(): void
    {
        $blade = '@php $name = "John Doe"; echo "Hello $name"; @endphp';
        $this->assertEquals(
            'Hello John Doe',
            $this->renderBlade($blade)
        );
    }

    public function testDoesNotRenderDirectives(): void
    {
        $conditions = [
            [
                'blade' => '@php echo "@if(true) @endif"; @endphp',
                'output' => '@if(true) @endif',
            ],
            [
                'blade' => "@php echo '{!! date(\'Y\') !!}' @endphp",
                'output' => "{!! date('Y') !!}",
            ],
            [
                'blade' => '@php echo "{{ date(\'Y\') }}"; @endphp',
                'output' => "{{ date('Y') }}",
            ],
        ];

        foreach ($conditions as $condition) {
            $this->assertEquals(
                $condition['output'],
                $this->renderBlade($condition['blade'])
            );
        }
    }

    public function testPhpTagsAreAllowed(): void
    {
        $this->assertSame(
            "Hello",
            $this->renderBlade("<?php echo 'Hello'; ?>")
        );
    }

    public function testDoesNotCompileDirectivesInPhp(): void
    {
        $template = "@if(true) hello @endif <?php echo '{{ 1 + 1 }}'; ?>";

        $this->assertSame(
            'hello {{ 1 + 1 }}',
            $this->removeIndentation($this->renderBlade($template))
        );
    }

    public function testOutputsPhpTagsInPhp(): void
    {
        $template = "<?php echo '<?php hello_world ?>';?>";

        $this->assertSame(
            "<?php hello_world ?>",
            $this->renderBlade($template)
        );
    }

    public function testCommentsTakesPrecedenceOverPhp(): void
    {
        $templates = [
            "{{-- @php echo 'HELLO WORLD' @endphp --}}",
            "{{-- <?php echo 'Hello world' ?> --}}",
        ];

        foreach ($templates as $template) {
            $this->assertEmpty($this->renderBlade($template));
        }
    }

    public function testShortEchoTagsAreAllowed(): void
    {
        $templates = [
            // Short echo on its own.
            [
                'blade' => '<?= "Hello" ?>',
                'output' => 'Hello',
            ],

            // Short echo immediately followed by inline HTML: the closing tag
            // must not become a stray @endphp while the opening tag survives.
            [
                'blade' => '<?= "x" ?>y',
                'output' => 'xy'
            ],

            // Short echo inside an HTML attribute value.
            [
                'blade' => '<a href="<?= "x" ?>y">z</a>',
                'output' => '<a href="xy">z</a>',
            ],

            // Short echo reading passed data, with a trailing element.
            [
                'blade' => '<div><?= $value ?>tail</div>',
                'output' => '<div>Valuetail</div>',
            ],

            // Consecutive short echoes.
            [
                'blade' => '<?= "a" ?><?= "b" ?>',
                'output' => 'ab',
            ],

            // Short and long echo mixed in one template.
            [
                'blade' => '<?= "a" ?>-<?php echo "b"; ?>',
                'output' => 'a-b',
            ],
        ];

        foreach ($templates as $template) {
            $this->assertSame(
                $template['output'],
                $this->renderBlade(
                    $template['blade'],
                    ['value' => 'Value'],
                ),
            );
        }
    }

    public function testDoesNotCompileDirectivesInShortEcho(): void
    {
        $templates = [
            [
                'blade' => '<?= "{{ 1 + 1 }}" ?>',
                'output' => '{{ 1 + 1 }}',
            ],
            [
                'blade' => '<?= "@if(true) @endif" ?>',
                'output' => '@if(true) @endif',
            ],
        ];

        foreach ($templates as $template) {
            $this->assertSame(
                $template['output'],
                $this->renderBlade($template['blade']),
            );
        }
    }

    public function testInlinePhpDirective(): void
    {
        /**
         * Multiple statements in inline @php directives
         * are not supported to maintain compatibility
         * with Laravel Blade.
         */
        $template = '@php($name = "Maria") {{ $name }}';

        $this->assertSame(
            'Maria',
            $this->removeIndentation($this->renderBlade($template))
        );

        $templateWithoutSpace = '@php($name = "No spaces"){{ $name }}';

        $this->assertSame(
            'No spaces',
            $this->removeIndentation($this->renderBlade($templateWithoutSpace))
        );
    }
}
