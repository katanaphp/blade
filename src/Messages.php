<?php

namespace Blade;

class Messages
{
    public const ERROR_CACHE_PATH_REQUIRED = 'Missing argument $cachePath';
    public const ERROR_VIEW_PATH_REQUIRED = 'Missing argument $viewPath';
    public const ERROR_EMPTY_VIEW_NAME = 'View name cannot be empty';
    public const ERROR_VIEW_NOT_FOUND = 'View file does not exist: %s';
    public const ERROR_VIEW_PATH_CONFLICT = 'View path cannot be used when config has view finders. Use Config::addViewPath instead.';
    public const ERROR_CACHE_PATH_CONFLICT = 'Cache path parameter cannot be used when config has cache path set.';
    public const ERROR_MISSING_DEFAULT_VIEW_FINDER = 'Missing default views, use Config::addViewPath';
    public const ERROR_AUTH_CALLBACK_REQUIRED = 'Auth callback is required, use Config::setAuthCallback';
    public const ERROR_MULTIPLE_PATH_FOR_NAMESPACE_NOT_ALLOWED = "Path for the component namespace %s already registered, multiple paths are not allowed";
    public const ERROR_INTERSECTION_TYPES_NOT_SUPPORTED = "Union types for resoluons not supported";
}
