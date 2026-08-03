<?php

namespace Blade;

class ComponentTagsCompiler
{
    public const REGEX_COMPONENT_NAME = "(?>(?>(?'namespace'[a-zA-Z0-9-]+)(?'separator'::))|)(?'name'[a-zA-Z0-9-.]+)";

    public function __construct(protected string $template) {}

    public function compile()
    {
        $result = $this->compileSelfClosingTags($this->template);
        $result = $this->compileOpeningTags($result);
        $result = $this->compileClosingTags($result);

        return $result;
    }

    protected function compileSelfClosingTags(string $template): string
    {
        $regex = self::REGEX_COMPONENT_NAME;

        $regex = <<<REGEX
        /<x-{$regex}
        \s*
        (?'attributes'
           (?>[\w\:\-\$]+
           (?:=
             (?>
               "[^"]*" |
               '[^']*' |
               [\w\$]+
             )\s*|
           )\s*
          )*
        )\s*(\/>)
        /x
        REGEX;

        return preg_replace_callback(
            $regex,
            function ($matches) {
                $attributes = $this->parseAttributes($matches['attributes']);
                return self::getStartRenderingCode($matches['namespace'] ?? '', $matches['name'], $attributes) .
                    self::getEndRenderingCode();
            },
            $template
        );
    }

    public static function getStartRenderingCode(string $namespace, string $componentName, string $attributes, bool $componentDirectiveCompatibility = false): string
    {
        $compatibilityFlag = sprintf("componentDirectiveCompatibility: %s", $componentDirectiveCompatibility ? "true" : "false");
        $attributes = trim($attributes);
        $attributes = $attributes ? $attributes : '[]';

        if ($componentName === 'slot') {
            return  "<?php \$component_renderer->beginSlot('$componentName', {$attributes});?>" .
                "<?php \$component = \$component_renderer->getLastComponent(); ?>";
        }

        return "<?php \$component_renderer->prepare('$namespace', '$componentName', {$attributes}, {$compatibilityFlag});?>";
    }

    public static function getEndRenderingCode(): string
    {
        return "<?php echo \$component_renderer->render(); \$component_renderer->popComponent(); ?>";
    }

    protected function parseAttributes(string $attribute): string
    {
        if ($attribute === '') {
            return '';
        }

        $attributes =   preg_replace_callback(
            <<<REGEX
            /(
              (?'name'[\w\:\-\$]+)
               (=
               (?'value'
                 (?>
                   "[^"]*" |
                   '[^']*' |
                   [\w\$]+
                 )
               )
              )?
            )/x
            REGEX,
            function ($matches) {
                $name = $matches['name'];
                $value = $matches['value'] ?? null;

                /**
                 * Check if the value is using an output expression
                 * `{{ ..... }}`. If it is, we need to convert it to
                 * concatinated string.
                 */
                if ($value && preg_match(Compiler::REGEX_OUTPUT_DIRECTIVE, $value) === 1) {

                    $quote = substr($value, 0, 1);

                    $value = preg_replace(
                        Compiler::REGEX_OUTPUT_DIRECTIVE,
                        $quote . " . $2 . " . $quote,
                        $value
                    );
                }

                if (str_starts_with($name, ':$')) {
                    $value = substr($name, 1);
                    $name = substr($name, 2);
                }

                if ($this->isExpressionAttribute($name)) {
                    $name = substr($name, 1);
                    $value = trim($value, '"');
                    $value = $this->trimQuotes($value);

                    return "'{$name}' => {$value},";
                }

                if (str_starts_with($name, '::')) {
                    $name = substr($name, 1);
                }

                if ($value) {
                    return "'{$name}' => {$value},";
                }

                return "'{$name}' => true,";
            },
            $attribute
        );

        return "[ $attributes ]";
    }

    /**
     * Check if the attribute value is an expression
     *
     * @param string $name
     * @return bool
     */
    private function isExpressionAttribute(string $name): bool
    {
        return str_starts_with($name, ':') && !str_starts_with($name, '::');
    }

    private function trimQuotes(string $value): string
    {
        if (str_starts_with($value, '\'')) {
            return trim($value, '\'');
        }

        return $value;
    }

    protected function compileOpeningTags(string $template): string
    {
        $regex = self::REGEX_COMPONENT_NAME;

        $regex = <<<REGEX
        /<x-{$regex}
        \s*
        (?'attributes'
           (?>[\w\:\-]+
           (?:=
             (?>
               "[^"]*" |
               '[^']*' |
               [\w\$]+
             )\s*|
           \s*)
          )*
        )\s*(>)
        /x
        REGEX;

        return preg_replace_callback(
            $regex,
            function ($matches) {

                return self::getStartRenderingCode(
                    $matches['namespace'] ?? '',
                    $matches['name'],
                    !empty($matches['attributes']) ? $this->parseAttributes($matches['attributes']) : '[]'
                );
            },
            $template
        );
    }

    protected function compileClosingTags(string $template): string
    {
        $regex = self::REGEX_COMPONENT_NAME;

        $regex = "/<\/x-{$regex}>/";

        return preg_replace_callback(
            $regex,
            function (array $matches) {
                if ($matches['name'] === 'slot') {
                    return "<?php unset(\$component); ?>" . "<?php \$component_renderer->endSlot(); ?>";
                }

                return self::getEndRenderingCode();
            },
            $template
        );
    }
}
