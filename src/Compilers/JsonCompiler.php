<?php

namespace Blade\Compilers;

use Blade\CompileAtRules;

/**
 *
 * @mixin CompileAtRules
 */
trait JsonCompiler
{

    public function compileJson(string $expression): string
    {
        $defaultFlags = JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT;

        $params = explode(',', $this->trimParenthesis($expression));

        $data = $params[0];

        $flags = isset($params[1]) ? $params[1] : $defaultFlags;
        $depth = isset($params[2]) ? $params[2] : 512;

        return sprintf("<?php echo json_encode(%s, %s, %s); ?>", $data, $flags, $depth);
    }
}
