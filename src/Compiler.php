<?php

namespace Blade;

use Exception;
use PhpToken;

class Compiler
{
    public const REGEX_OUTPUT_DIRECTIVE = "/(@|){{\s*(?'expression'(?:\s+|.)*?)\s*}}/";

    /**
     * Stores blocks of code that must
     * be rendered as is. E.g @verbatim
     *
     * @var array
     */
    protected array $contentBlocks = [];

    public function __construct(protected string $template, protected Blade $blade) {}


    public function compile()
    {
        $result = $this->template;

        $result = $this->preprocessStaticBlocks($result);
        $result = $this->compileCommentDirective($result);
        $result = (new ComponentTagsCompiler($result))->compile();
        $result = (new CompileAtRules($result, $this->blade))->compile();
        $result = $this->compileComponentAttributes($result);
        $result = $this->compileOutputDirective($result);
        // $result = $this->compileOutputDirective($result);
        $result = $this->compileUnsafeOutputDirective($result);
        $result = $this->compileOnceDirective($result);

        $result = $this->replaceContentBlocks($result);

        return $result;
    }

    /**
     * Replaces block that do not require
     * compilation such as @verbatim and @php
     * with a unique identifier.
     *
     * E.g @verbatim, @php
     *
     * @param string $template
     * @return string
     */
    protected function preprocessStaticBlocks(string $template): string
    {
        $template = $this->compileVerbatimDirective($template);
        $template = $this->compilePhpBlock($template);

        return $template;
    }

    /**
     * Compiles the @verbatim directive.
     *
     * @param string $template
     * @return string
     */
    protected function compileVerbatimDirective(string $template): string
    {
        return preg_replace_callback(
            "/@verbatim(?'content'(?:\s|.)*?)@endverbatim/",
            function (array $matches) {
                $content = $matches['content'];
                $identifier = md5($content);

                $this->contentBlocks[$identifier] = $content;

                return $this->getContentBlockTag($identifier);
            },
            $template
        );
    }

    /**
     * Compiles the @php directive.
     *
     * @param string $template
     * @return string
     */
    protected function compilePhpBlock(string $template): string
    {
        $template = $this->replacePhpTagsWithDirectives($template);

        return preg_replace_callback(
            "/@php(?'content'(?:\s|.)*?)@endphp/",
            function (array $matches) {
                $content = $matches['content'];
                $identifier = md5($content);

                $this->contentBlocks[$identifier] = "<?php $content ?>";

                return $this->getContentBlockTag($identifier);
            },
            $template
        );
    }

    protected function replacePhpTagsWithDirectives(string $template): string
    {
        return array_reduce(token_get_all($template), function (string $template, string|array $token) {
            if (is_array($token)) {
                $template .= match ($token[0]) {
                    T_OPEN_TAG => "@php",
                    T_OPEN_TAG_WITH_ECHO => "@php echo ",
                    T_CLOSE_TAG => "@endphp",
                    default => $token[1],
                };
            } else {
                $template .= $token;
            }

            return $template;
        }, '');
    }

    protected function getContentBlockTag(string $identifier): string
    {
        return sprintf(
            "##CONTENT_BLOCK %s ###",
            $identifier
        );
    }

    protected function replaceContentBlocks(string $template): string
    {
        foreach ($this->contentBlocks as $identifier => $content) {
            $template = str_replace(
                $this->getContentBlockTag($identifier),
                $content,
                $template,
            );
        }

        return $template;
    }

    protected function compileComponentAttributes(string $template): string
    {
        return $template;
    }

    protected function compileCommentDirective(string $template)
    {
        return preg_replace("/{{--([\s]*?(.|\s)*?[\s]*?)--}}/", '', $template);
    }


    protected function compileOutputDirective(string $template)
    {
        return preg_replace_callback(self::REGEX_OUTPUT_DIRECTIVE, function (array $matches) {
            $directive = $matches[0];
            $expression = $matches['expression'];

            if (str_starts_with($directive, '@')) {
                return substr($directive, 1);
            }

            return "<?php echo \Blade\\e($expression, \$__env->config); ?>";
        }, $template);
    }

    protected function compileUnsafeOutputDirective(string $template)
    {
        return preg_replace_callback("/(@|){!!(?'expression'(\s|.)*?)!!}/",  function (array $matches) {
            $directive = $matches[0];
            $expression = $matches['expression'];

            if (str_starts_with($directive, '@')) {
                return substr($directive, 1);
            }

            return "<?php echo $expression; ?>";
        }, $template);
    }

    protected function compileOnceDirective(string $template)
    {
        do {
            $template = preg_replace_callback(
                "/@once(?'content'(?:\s|.)*?)@endonce/",
                function (array $matches) use ($template) {
                    $content = $matches[0];
                    $identifier = md5($content . strpos($template, '@once'));

                    $content = str_replace(
                        '@once',
                        "<?php if(!\$template_renderer->hasRendered('$identifier')): ?>" .
                            "<?php \$template_renderer->markAsRendered('$identifier'); ?>",
                        $content
                    );

                    $content = str_replace('@endonce', '<?php endif; ?>', $content);

                    return $content;
                },
                $template,
                1
            );
        } while (preg_match("/@once/", $template));

        return $template;
    }
}
