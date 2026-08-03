<?php

namespace Blade;

class TemplateInheritanceRenderer
{
    protected string $template = '';
    protected array $sections = [];
    protected bool $renderingParentLayout = false;
    protected string $currentSection = '';
    protected ?array $tempContextData = null;
    protected array $rendered = [];

    public function __construct(protected Blade $blade) {}

    public function extends(string $template): void
    {
        $this->template = $template;
        ob_start();
    }

    public function yield(string $section, string $fallbackContent = ''): string
    {
        if ($this->hasSection($section)) {
            return $this->getSection($section);
        }

        return  $fallbackContent;
    }

    public function startSection(string $section, string $inlineContent = ''): void
    {
        if (!$inlineContent) {
            ob_start();
        }

        $this->currentSection = $section;

        if (isset($this->sections[$section])) {
            return;
        }

        $this->sections[$section] = (object) [
            'content' => $inlineContent ? $inlineContent : '',
            'defaultContent' => '',
        ];
    }

    public function endSection(): void
    {

        $section = $this->sections[$this->currentSection];

        if ($section->content === '') {
            $section->content = ob_get_clean();
        } else {
            $section->defaultContent = ob_get_clean();
        }
    }

    public function hasSection(string $section): bool
    {
        return isset($this->sections[$section]);
    }

    protected function getSection(string $name): string
    {
        $section = $this->sections[$name];

        return $section->content ? $section->content : $section->defaultContent;
    }

    public function outputSection(string $type = 'default_content'): void
    {
        $this->endSection();

        $section = $this->sections[$this->currentSection];
        $content = $this->getSection($this->currentSection);

        if ($this->hasParentContentPlaceholder($content)) {
            $content = $this->replaceParentContentPlaceholder($content, $section);
        }

        echo $content;
    }

    protected function hasParentContentPlaceholder(string $content): bool
    {
        return strpos($content, '### DEFAULT SECTION CONTENT ###') !== false;
    }

    protected function replaceParentContentPlaceholder(string $content, object $section): string
    {
        return str_replace(
            '### DEFAULT SECTION CONTENT ###',
            $section->defaultContent,
            $content
        );
    }

    public function output(): void
    {
        $output = ob_get_clean();
        if (trim($output) !== '') {
            echo $output;
        }

        $this->renderingParentLayout = true;

        $defaultData = $this->tempContextData ?? [];
        $this->tempContextData = null;

        echo $this->blade->render($this->template, $defaultData);
    }

    public function include(string $template, array $data = [])
    {
        $defaultData = $this->tempContextData ?? [];
        $this->tempContextData = null;

        echo $this->blade->render(
            $template,
            array_merge($defaultData, $data)
        );
    }

    public function includeIf(string $template, array $data = []): void
    {
        if (!$this->blade->viewExists($template)) {
            return;
        }

        $this->include($template, $data);
    }

    public function includeWhen(bool $condition, string $template, array $data = []): void
    {
        if (!$condition) {
            return;
        }

        $this->include($template, $data);
    }

    public function includeUnless(bool $condition, string $template, array $data = []): void
    {
        if ($condition) {
            return;
        }

        $this->include($template, $data);
    }

    public function includeFirst(array $views, array $data = []): void
    {
        $toRender = null;

        foreach ($views as $view) {
            if ($this->blade->viewExists($view)) {
                $toRender = $view;
                break;
            }
        }

        if ($toRender) {
            $this->include($toRender, $data);
        }
    }

    public function withDefault(array $data): static
    {
        unset($data['template_renderer']);
        unset($data['component_renderer']);

        $this->tempContextData = $data;

        return $this;
    }

    public function renderEach(string $template, array $data, string $valueVariable = 'value', string $emptyTemplate = ''): void
    {
        if (empty($valueVariable)) {
            $valueVariable = 'value';
        }

        $rendered = false;

        foreach ($data as $key => $value) {
            $rendered = true;

            $this->include($template, [
                'key' => $key,
                $valueVariable => $value,
            ]);
        }


        if (!$rendered && $emptyTemplate) {
            $this->include($emptyTemplate);
        }
    }

    public function hasRendered(string $identifier): bool
    {
        return isset($this->rendered[$identifier]);
    }

    public function markAsRendered(string $identifier): void
    {
        $this->rendered[$identifier] = true;
    }
}
