# Blade compatibility tests

Tests that render templates with Laravel Blade and with Katana then compare the results.
Laravel is provided by [Orchestra Testbench](https://packages.tools/testbench/), so there is no application skeleton needed.

The suite has its own `composer.json` and is independent of the package in the repository root.

```sh
composer install
composer test
```
