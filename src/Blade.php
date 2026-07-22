<?php

namespace Blade;

use Blade\Environments\FragmentEnvironment;
use Blade\Environments\StackEnvironment;
use Blade\Exceptions\BladeException;
use Closure;
use Exception;
use InvalidArgumentException;

final class Blade
{
    public const RESERVED_DIRECTIVES = [
        'env',
        'production',
    ];

    public const DEFAULT_APP_ENVIRONMENT = 'production';

    public array $fragments = [];
    public ComponentRenderer $componentRenderer;
    public TemplateInheritanceRenderer $templateRenderer;

    public string $cachePath;

    public Config $config;

    /**
     *
     */
    protected array $directives = [];

    use FragmentEnvironment;
    use StackEnvironment;

    public function __construct(?string $viewPath = null, ?string $cachePath = null, ?Config $config = null)
    {
        if (!$viewPath && !$config) {
            throw new BladeException(Messages::ERROR_VIEW_PATH_REQUIRED);
        } elseif ($viewPath && $config && count($config->getViewFinders()) > 0) {
            throw new BladeException(Messages::ERROR_VIEW_PATH_CONFLICT);
        }

        if (!$cachePath && !$config) {
            throw new BladeException(Messages::ERROR_CACHE_PATH_REQUIRED);
        } elseif ($cachePath && $config) {
            throw new BladeException(Messages::ERROR_CACHE_PATH_CONFLICT);
        }

        $this->config = ($config ?? new Config($cachePath));

        if ($viewPath) {
            $this->config->addViewFinder(new FileSystemViewFinder($viewPath));
        }

        if (empty($this->config->getViewFinders())) {
            throw new BladeException(Messages::ERROR_MISSING_DEFAULT_VIEW_FINDER);
        }

        /**
         *
         * Always assume application is running
         * in production unless specified.
         */
        $this->setEnvironment(fn() => self::DEFAULT_APP_ENVIRONMENT);
        $this->setDirective('production', fn() => $this->getDirective('env')(self::DEFAULT_APP_ENVIRONMENT));


        $this->componentRenderer = new ComponentRenderer($this);
        $this->templateRenderer = new TemplateInheritanceRenderer($this);
    }

    public function getDirective(string $name): ?callable
    {
        return $this->directives[$name] ?? $this->config->getDirective($name);
    }

    public function runDirective($name, ...$params): mixed
    {
        $callback = $this->getDirective($name);

        if (!$callback) {
            return false;
        }

        return $callback(...$params);
    }

    public function registerDirective(string $name, Closure $callback): static
    {
        if (in_array($name, self::RESERVED_DIRECTIVES)) {
            throw new InvalidArgumentException("{$name} directive is not allowed");
        }

        return $this->setDirective($name, $callback);
    }

    protected function setDirective(string $name, Closure $callback): static
    {
        $this->directives[$name] = $callback;

        return $this;
    }

    /**
     * @param Closure(): string $callback
     */
    public function setEnvironment(callable $callback): static
    {
        return $this->setDirective('env', function (string | array $environment) use ($callback) {
            $environment = is_array($environment) ? $environment : [$environment];

            return in_array($callback(), $environment);
        });
    }

    /**
     * Register a path or view finder for anonymous components other than
     * the default view path.
     */
    public function addAnonymousComponentPath(string|ViewFinder $path, string $namespace = ''): self
    {
        if (is_string($path)) {
            $this->config->addAnonymousComponentPath($path, $namespace);
        } else {
            $this->config->addAnonymousComponentViewFinder($path, $namespace);
        }

        return $this;
    }

    public function addAnonymousComponentViewFinder(ViewFinder $finder, string $namespace = ''): self
    {
        $this->config->addAnonymousComponentViewFinder($finder, $namespace);

        return $this;
    }

    public function compile(string | Component $view): string
    {
        if (!$this->viewExists($view)) {
            $componentName = is_string($view) ? $view : implode('::', array_filter([$view->namespace, $view->name]));

            throw new BladeException(
                sprintf(Messages::ERROR_VIEW_NOT_FOUND, $componentName)
            );
        }

        $identifier = $this->getViewIdentifier($view);
        $compiledPath = $this->getCachedViewPath($identifier);

        if (file_exists($compiledPath)) {
            return $identifier;
        }

        $complied = (new Compiler($this->getViewContents($view), $this))->compile();
        $complied .= "<?php ##PATH  ## ?>";

        $this->saveCache($identifier, $complied);

        return $identifier;
    }

    public function getViewIdentifier(string | Component $view): string
    {
        $name = is_string($view) ? $view : $view->name;

        return hash('sha1', $name . $this->getLastModified($view));
    }

    protected function getLastModified(string | Component $view): int
    {
        if ($view instanceof Component) {
            return $view->lastModified();
        }

        $finder = $this->resolveFinder($view);
        if (!$finder) {
            throw new BladeException(sprintf(Messages::ERROR_VIEW_NOT_FOUND, $view));
        }

        return $finder->lastModified($view);
    }

    protected function getViewContents(string | Component $name): string
    {
        if ($name instanceof Component) {
            return $name->getContents();
        }

        $finder = $this->resolveFinder($name);

        if (!$finder) {
            throw new BladeException(sprintf(Messages::ERROR_VIEW_NOT_FOUND, $name));
        }

        return $finder->getContents($name);
    }

    public function render(string | Component $view, array $data = []): View
    {
        return new View($this, $view, $data);
    }

    public function renderContents(string | Component $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $component_renderer = $this->componentRenderer;
        $template_renderer = $this->templateRenderer;
        $__env = $this;

        try {
            include $this->getCachedViewPath($this->compile($view));
        } catch (Exception $e) {
            /**
             * PHPUnit will mark tests as risky when output
             * buffers are not properly closed, when exceptions
             * are thrown during tests.
             */
            if (ob_get_level()) {
                ob_get_clean();
            }

            throw $e;
        }
    }

    protected function resolveFinder(string $name): ?ViewFinder
    {
        foreach ($this->config->getViewFinders() as $finder) {
            if ($finder->viewExists($name)) {
                return $finder;
            }
        }

        return null;
    }

    public function viewExists(string | Component $view, bool $isComponent = false): bool
    {
        if ($view instanceof Component) {
            return $view->viewExists();
        }

        return $this->resolveFinder($view) !== null;
    }

    protected function getCachedViewPath(string $identifier): string
    {
        return sprintf(
            '%s/%s.php',
            rtrim($this->config->cachePath, '/'),
            $identifier
        );
    }

    private function saveCache(string $identifier, string $compiled): void
    {
        file_put_contents(
            $this->getCachedViewPath($identifier),
            $compiled
        );
    }

    /**
     * Filters conditional values.
     *
     * @param string[] $values
     * @return string[]
     */
    public static function filterConditionalValues(array $values): array
    {
        $applicable = [];

        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $applicable[] = $value;
                continue;
            }

            if ($value) {
                $applicable[] = $key;
            }
        }

        return $applicable;
    }

    /**
     * Compiles the @class directive.
     *
     * @param array $classes
     * @return string
     */
    public static function classAttribute(array $classes): string
    {
        return sprintf(
            'class="%s"',
            implode(' ', static::filterConditionalValues($classes))
        );
    }

    public static function styleAttribute(array $styles): string
    {
        $styles = static::filterConditionalValues($styles);

        $styles = array_map(function ($style) {
            return rtrim($style, ';') . ';';
        }, $styles);

        return sprintf(
            'style="%s"',
            implode(' ', static::filterConditionalValues($styles))
        );
    }
}
