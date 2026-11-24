<?php

namespace Blade\Compilers;

use Blade\CompileAtRules;
use Blade\Exceptions\BladeException;
use Blade\Messages;

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

        $flags = trim($params[1] ?? (string) $defaultFlags);
        $depth = trim($params[2] ?? '512');

        if (!$this->isJsonFlags($flags)) {
            throw new BladeException(Messages::ERROR_COMPLEX_JSON_EXPRESSION);
        }

        // json_encode() itself only accepts a depth between 1 and the 32 bit integer limit.
        $minDepth = 1;
        $maxDepth = 0x7FFFFFFF;

        if (!is_numeric($depth) || $depth < $minDepth || $depth > $maxDepth) {
            throw new BladeException(sprintf(Messages::ERROR_INVALID_JSON_DEPTH, $depth, $minDepth, $maxDepth));
        }

        return sprintf("<?php echo json_encode(%s, %s, %s); ?>", $data, $flags, $depth);
    }

    /**
     * Flags are integers or defined JSON_* constants, optionally joined by "|".
     */
    private function isJsonFlags(string $flags): bool
    {
        foreach (explode('|', $flags) as $flag) {
            $flag = trim($flag);

            $isJsonConstant = str_starts_with($flag, 'JSON_') && defined($flag);

            if (!is_numeric($flag) && !$isJsonConstant) {
                return false;
            }
        }

        return true;
    }
}
