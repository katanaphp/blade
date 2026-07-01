<?php

namespace Blade;

use Blade\Exceptions\BladeException;
use Blade\Messages;
use Override;

class FileSystemViewFinder extends ViewFinder
{
    public function __construct(public string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    #[Override]
    public function viewExists(string $name): bool
    {
        if ($name === '') {
            throw new BladeException(Messages::ERROR_EMPTY_VIEW_NAME);
        }

        return file_exists($this->getFilePath($name));
    }

    #[Override]
    public function lastModified(string $name): int
    {
        if (!$this->viewExists($name)) {
            throw new BladeException(sprintf(Messages::ERROR_VIEW_NOT_FOUND, $name));
        }

        return filemtime($this->getFilePath($name));
    }

    #[Override]
    public function getContents(string $name): string
    {
        if (!$this->viewExists($name)) {
            throw new BladeException(sprintf(Messages::ERROR_VIEW_NOT_FOUND, $name));
        }

        return file_get_contents($this->getFilePath($name));
    }

    protected function getFilePath(string $viewName): string
    {
        return sprintf(
            "%s/%s.blade.php",
            $this->basePath,
            str_replace('.', '/', $viewName)
        );
    }
}
