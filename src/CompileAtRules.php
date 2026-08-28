<?php

namespace Blade;

use Blade\Compilers\FragmentCompiler;
use Blade\Compilers\StackCompiler;
use Blade\Environments\FragmentEnvironment;

class CompileAtRules
{
    const FORELSE_CLOSED = 0;
    const FORELSE_OPEN = 1;
    const FORELSE_OPEN_EMPTY_BLOCK = 2;

    use CompileForeachTrait;
    use FragmentCompiler;
    use StackCompiler;

    protected bool $switchOpen = false;
    protected bool $switchFirstCaseClosed = false;
    protected bool $usesTemplateInheritance = false;
    protected int $forelseStatus = self::FORELSE_CLOSED;

    public function __construct(protected string $content, protected Blade $blade) {}


    public function compile(): string
    {
        $statementRegex = "/(@|)@(?'directive'[a-z]+)\s*(?'expression'\((?:\s|.)*?\))?/i";

        $matches = [];

        preg_match_all($statementRegex, $this->content, $matches);

        if (empty($matches)) {
            return $this->content;
        }

        foreach ($matches[0] as $directive) {
            /**
             * It is going ro be tricky as
             * we are unable to get correct
             * number of parenthesis using regex.
             *
             * So, we are going to loop and reconstruct
             * the expression.
             *
             * @todo Revisit regex to get correct number of parenthesis
             */
            $openParenthesisCount = substr_count($directive, '(',);
            $closeParenthesisCount = substr_count($directive, ')');

            if ($openParenthesisCount !== $closeParenthesisCount) {
                $start = strpos($this->content, $directive) + strlen($directive);

                do {
                    $nextChar = substr($this->content, $start, 1);

                    if ($nextChar === '(') {
                        $openParenthesisCount++;
                    }

                    if ($nextChar === ')') {
                        $closeParenthesisCount++;
                    }

                    $directive .= $nextChar;

                    $start++;
                } while ($openParenthesisCount !== $closeParenthesisCount);
            }

            if (str_starts_with($directive, '@@')) {
                $this->content = $this->replaceDirective(
                    $directive,
                    substr($directive, 1),
                    $this->content
                );
                continue;
            }

            $this->content = $this->compileDirective($directive, $this->content);
        }

        if ($this->usesTemplateInheritance) {
            $this->content .= $this->endExtends();
        }

        return $this->content;
    }

    protected function compileDirective(string $directive, string $content): string
    {
        $directiveName = '';

        if (str_contains($directive, '(')) {
            $directiveName = substr(
                strstr($directive, '(', true),
                offset: 1
            );
        } else {
            $directiveName = substr($directive, offset: 1);
        }

        $directiveName = trim($directiveName);

        $expression = substr(
            $directive,
            offset: strlen($directiveName) + 1,
        );

        if (empty($directiveName)) {
            return $content;
        }

        $methodName = sprintf("compile%s", ucfirst($directiveName));

        if (method_exists($this, $methodName)) {
            $content = $this->replaceDirective(
                $directive,
                $this->{$methodName}($expression),
                $content
            );
        } elseif ($this->blade->getDirective($directiveName)) {
            $callback = $this->blade->getDirective($directiveName);

            $expression = trim($expression, "(");
            $expression = trim($expression, ")");

            if (!$callback->isConditional) {
                $content = $this->replaceDirective(
                    $directive,
                    $callback($expression),
                    $content
                );
            } elseif ($callback) {
                $content = $this->replaceDirective(
                    $directive,
                    "<?php if(\$__env->runDirective('{$directiveName}', {$expression})): ?>",
                    $content,
                );
            }
        } elseif (str_starts_with($directiveName, 'end') && $this->blade->getDirective(substr($directiveName, 3))) {
            $content = $this->replaceDirective(
                $directive,
                "<?php endif; ?>",
                $content
            );
        }

        return $content;
    }

    protected function replaceDirective(string $directive, string $replacement, string $content): string
    {
        $before = strstr($content, $directive, before_needle: true);

        return  $before . $replacement . substr($content, strlen($before) + strlen($directive));
    }

    protected function compileIf(string $expression): string
    {
        return "<?php if{$expression}: ?>";
    }

    protected function compileElseif(string $expression): string
    {
        return "<?php elseif{$expression}: ?>";
    }

    protected function compileEndif(string $expression): string
    {
        return "<?php endif; ?>";
    }

    protected function compileElse(string $expression): string
    {
        return "<?php else: ?>";
    }

    protected function compilePhp(string $expression): string
    {
        return strlen($expression) === 0 ? "<?php " : "<?php $expression ?>";
    }

    protected function compileEndphp(string $expression): string
    {
        return " ?>";
    }

    /**
     * Compiles the @props directive.
     */
    protected function compileProps(string $expression): string
    {
        return "<?php \$component_renderer->setProps({$expression});" .
            "extract(\$component_renderer->getViewData()); ?>";
    }

    /**
     * Compiles the @class directive.
     *
     * @param string $expression
     * @return string
     */
    protected function compileClass(string $expression): string
    {
        return "<?php echo \Blade\Blade::classAttribute({$expression}); ?>";
    }

    protected function compileStyle(string $expression): string
    {
        return "<?php echo \Blade\Blade::styleAttribute({$expression}); ?>";
    }

    protected function compileContinue(string $expression): string
    {
        return "<?php continue; ?>";
    }

    protected function compileIsset(string $expression): string
    {
        return "<?php if(isset{$expression}): ?>";
    }

    protected function compileEndIsset(string $expression): string
    {
        return $this->compileEndif($expression);
    }

    protected function compileEmpty(string $expression): string
    {
        if ($this->forelseStatus === self::FORELSE_OPEN) {
            $this->forelseStatus = self::FORELSE_OPEN_EMPTY_BLOCK;
            return '<?php endforeach; ?>' .
                '<?php if(!$__forelse_looped): ?>';
        }

        return "<?php if(empty{$expression}): ?>";
    }

    protected function compileEndempty(string $expression): string
    {
        return $this->compileEndif($expression);
    }

    protected function compileSwitch(string $expression): string
    {
        $this->switchOpen = true;
        $this->switchFirstCaseClosed = false;

        /**
         * We are going to leave the switch open
         * and close them when we encounter the
         * first case to prevent PHP parse error.
         *
         * @todo Revisit this approach, it may
         * conflict only default case is used.
         */
        return "<?php switch{$expression}: ";
    }

    protected function compileCase(string $expression): string
    {

        if (!$this->switchFirstCaseClosed) {
            $this->switchFirstCaseClosed = true;
            return "case{$expression}: ?>";
        }

        return "<?php case{$expression}: ?>";
    }

    protected function compileDefault(string $expression): string
    {
        if (!$this->switchFirstCaseClosed) {
            $this->switchFirstCaseClosed = true;
            return "default: ?>";
        }

        return "<?php default: ?>";
    }

    protected function compileBreak(string $expression): string
    {
        return "<?php break; ?>";
    }

    protected function compileEndswitch(string $expression): string
    {
        $this->switchOpen = false;

        if (!$this->switchFirstCaseClosed) {

            $this->switchFirstCaseClosed = false;

            return " endswitch; ?>";
        }

        $this->switchFirstCaseClosed = false;




        return "<?php endswitch; ?>";
    }


    protected function compileUnless(string $expression): string
    {
        return "<?php if(!($expression)): ?>";
    }

    protected function compileEndUnless(string $expression): string
    {
        return "<?php endif; ?>";
    }


    protected function compileFor(string $expression): string
    {
        return "<?php for{$expression}: ?>";
    }

    protected function compileEndfor(string $expression): string
    {
        return "<?php endfor; ?>";
    }

    protected function compileWhile(string $expression): string
    {
        return "<?php while{$expression}: ?>";
    }

    protected function compileEndwhile(string $expression): string
    {
        return "<?php endwhile; ?>";
    }

    protected function compileExtends(string $expression): string
    {
        $this->usesTemplateInheritance = true;

        return "<?php \$template_renderer->extends({$expression}); ?>";
    }

    protected function endExtends(): string
    {
        return "<?php \$template_renderer->withDefault(get_defined_vars())->output(); ?>";
    }

    protected function compileYield(string $expression): string
    {
        return "<?php echo \$template_renderer->yield{$expression}; ?>";
    }

    protected function compileSection(string $expression): string
    {
        return "<?php \$template_renderer->startSection{$expression}; ?>";
    }

    protected function compileEndsection(string $expression): string
    {
        return "<?php \$template_renderer->endSection(); ?>";
    }

    protected function compileShow(string $expression): string
    {
        return "<?php echo \$template_renderer->outputSection(); ?>";
    }

    protected function compileParent(string $expression): string
    {
        return "### DEFAULT SECTION CONTENT ###";
    }

    protected function compileHasSection(string $expression): string
    {
        return "<?php if(\$template_renderer->hasSection{$expression}): ?>";
    }

    protected function compileSectionMissing(string $expression): string
    {
        return "<?php if(!\$template_renderer->hasSection{$expression}): ?>";
    }

    protected function compileInclude(string $expression): string
    {
        return "<?php echo \$template_renderer->withDefault(get_defined_vars())" .
            "->include{$expression}; ?>";
    }

    protected function compileIncludeIsolated(string $expression): string
    {
        return "<?php echo \$template_renderer->include{$expression}; ?>";
    }

    protected function compileIncludeIf(string $expression): string
    {
        return "<?php echo \$template_renderer->withDefault(get_defined_vars())" .
            "->includeIf{$expression}; ?>";
    }

    protected function compileIncludeWhen(string $expression): string
    {
        return "<?php echo \$template_renderer->withDefault(get_defined_vars())" .
            "->includeWhen{$expression}; ?>";
    }

    protected function compileIncludeUnless(string $expression): string
    {
        return "<?php echo \$template_renderer->withDefault(get_defined_vars())" .
            "->includeUnless{$expression}; ?>";
    }

    protected function compileIncludeFirst(string $expression): string
    {
        return "<?php echo \$template_renderer->withDefault(get_defined_vars())" .
            "->includeFirst{$expression}; ?>";
    }

    public function compileRequired(string $expression): string
    {
        if (empty($expression)) {
            return "required";
        }

        return "<?php echo ($expression) ? 'required' : ''; ?>";
    }

    public function compileDisabled(string $expression): string
    {
        if (empty($expression)) {
            return "disabled";
        }

        return "<?php echo ($expression) ? 'disabled' : ''; ?>";
    }

    public function compileChecked(string $expression): string
    {
        if (empty($expression)) {
            return "checked";
        }

        return "<?php echo ($expression) ? 'checked' : ''; ?>";
    }

    public function compileSelected(string $expression): string
    {
        if (empty($expression)) {
            return "selected";
        }

        return "<?php echo ($expression) ? 'selected' : ''; ?>";
    }

    public function compileReadonly(string $expression): string
    {
        if (empty($expression)) {
            return "readonly";
        }

        return "<?php echo ($expression) ? 'readonly' : ''; ?>";
    }

    public function compileEach(string $expression): string
    {
        return '<?php $template_renderer->renderEach' . $expression . '; ?>';
    }

    public function compileForelse(string $expression): string
    {
        $this->forelseStatus = self::FORELSE_OPEN;

        return '<?php $__forelse_looped = false; ?>' .
            $this->compileForeach($expression) .
            '<?php $__forelse_looped = true; ?>';
    }

    public function compileEndforelse(string $expression): string
    {
        $output = '<?php endforeach; ?>';

        if ($this->forelseStatus === self::FORELSE_OPEN_EMPTY_BLOCK) {
            $output = "<?php endif; ?>";
        }

        $this->forelseStatus = self::FORELSE_CLOSED;

        return $output;
    }

    public function compileUse(string $expression): string
    {
        $expression = $this->trimParenthesis($expression);

        if (str_contains($expression, "{")) {
            $expression = trim($expression, "\ \'\"");
        } else {
            $expression = array_map(
                fn($part) => trim($part, "\ \'\""),
                explode(",", $expression)
            );
            $expression = implode(" as ", $expression);
        }

        return "<?php use {$expression}; ?>";
    }

    protected function trimParenthesis(string $string): string
    {
        if (str_starts_with($string, '(') && str_ends_with($string, ')')) {
            return substr($string, 1, strlen($string) - 2);
        }

        return $string;
    }

    public function compileSlot(string $expression): string
    {
        /**
         * Trim starting and ending parenthesis
         */
        $expression = substr($expression, 1);
        $expression = substr($expression, offset: 0, length: strlen($expression) - 1);

        $componentPart = explode(",", $expression, 2)[0];

        $slotName = trim($componentPart, '"');
        $slotName = trim($slotName, "'");

        $attributes = "[ 'name' => '{$slotName}' ]";

        return ComponentTagsCompiler::getStartRenderingCode('', 'slot', $attributes, true);
    }

    public function compileEndSlot(string $expression): string
    {
        return "<?php \$component_renderer->endSlot(); ?>";
    }

    public function compileComponent(string $expression): string
    {
        /**
         * Trim starting and ending parenthesis
         */
        $expression = substr($expression, 1);
        $expression = substr($expression, offset: 0, length: strlen($expression) - 1);

        $componentPart = explode(",", $expression, 2)[0];

        $componentName = trim($componentPart, '"');
        $componentName = trim($componentName, "'");

        $attributes = substr($expression, strlen($componentPart) + 1);

        return ComponentTagsCompiler::getStartRenderingCode('', $componentName, $attributes, true);
    }

    public function compileEndComponent(): string
    {
        return "<?php echo \$component_renderer->render(); ?>";
    }
}
