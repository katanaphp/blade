<?php

namespace Tests;

use Blade\Blade;
use Blade\Config;
use Blade\FileSystemViewFinder;
use Override;

trait VerifiesOutputTrait
{
    private array $createdFiles = [];

    protected Blade $blade;

    protected function setup(): void
    {
        parent::setUp();

        if (!is_dir($this->getTempDirectory())) {
            mkdir($this->getTempDirectory());
        }

        if (!is_dir($this->getCacheDirectory())) {
            mkdir($this->getCacheDirectory());
        }

        $viewFinder = new class($this->getTempDirectory()) extends FileSystemViewFinder {
            protected static int $time = 0;

            #[Override]
            public function lastModified(string $name): int
            {
                /**
                 * Incrementing number ensures a different
                 * modified time is returned for each render
                 * requiring recompilation in every test.
                 */
                return self::$time++;
            }
        };

        $config = (new Config($this->getCacheDirectory()));
        $config->addViewFinder($viewFinder);

        $this->blade = new Blade(config: $config);
    }


    public function tearDown(): void
    {
        $this->deleteDirectory($this->getCacheDirectory());
        $this->deleteDirectory($this->getTempDirectory());

        parent::tearDown();
    }

    protected function deleteDirectory(string $directory): void
    {
        $directory = rtrim($directory, '/');
        $files = glob("$directory/*");

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->deleteDirectory($file);
            }
        }

        is_dir($directory) && rmdir($directory);
    }

    private function getTempDirectory(): string
    {
        static $directory;

        if (is_null($directory)) {
            $directory = __DIR__ . '/tmp';
            sys_get_temp_dir();
        }



        return $directory;
    }

    /**
     * Undocumented function
     *
     * @param string $template
     * @return string
     */
    private function createTemporaryBladeFile(string $template, string $name = '', bool $isComponent = false): string
    {
        if (empty($name)) {
            $name = hash('sha256', $template);
        }

        if ($isComponent) {
            $name = 'components.' . $name;
        }

        if (str_contains($name, '.')) {
            $name = str_replace('.', '/', $name);
        }

        $file = $this->getTempDirectory() . "/{$name}.blade.php";


        if ($isComponent) {
            $directory = pathinfo($file, PATHINFO_DIRNAME);
            $this->maybeCreateComponentDirectory($directory);
        }


        if (file_put_contents($file, $template) === false) {
            throw new \Exception('Could not create temporary file');
        }

        $this->createdFiles[] = $file;

        return $name;
    }

    private function maybeCreateComponentDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir(
                directory: $directory,
                recursive: true
            );
        }
    }


    public function renderBlade($template, $data = [])
    {
        $name = $this->createTemporaryBladeFile(template: $template);

        return (string) $this->blade->render($name, $data);
    }

    public function createComponent(string $name, string $template, $data = [])
    {
        $name = $this->createTemporaryBladeFile(
            $template,
            $name,
            true
        );
    }

    protected function removeIndentation(string $input): string
    {
        return preg_replace('/\s+/', ' ', trim($input));
    }

    protected function getCacheDirectory(): string
    {
        return $this->getTempDirectory() . '/.cache';
    }
}
