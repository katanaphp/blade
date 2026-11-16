# Blade specification tests

A minimal Laravel application whose tests determine the behaviour of Laravel Blade.
It has its own `composer.json` and is independent of the package in the repository root.

```sh
composer install
cp .env.example .env && php artisan key:generate
composer test
```
