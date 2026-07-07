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
}
