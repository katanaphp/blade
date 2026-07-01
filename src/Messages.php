<?php

namespace Blade;

class Messages
{
    public const ERROR_INVALID_CONFIG = '(View and cache path) or config parameter must be present.';
    public const ERROR_CACHE_PATH_REQUIRED = 'Missing argument $cachePath';
    public const ERROR_VIEW_PATH_REQUIRED = 'Missing argument $viewPath';
    public const ERROR_EMPTY_VIEW_NAME = 'View name cannot be empty';
    public const ERROR_VIEW_NOT_FOUND = 'View file does not exist: %s';
}
