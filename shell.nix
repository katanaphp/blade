# Development shell for running the test suite.
#
# Some system PHP builds omit the dom/xml/xmlwriter extensions that PHPUnit
# needs, which makes `composer install` report an incompatible platform. The
# PHP shipped by nixpkgs enables them by default, so this shell gives a
# reproducible runner without touching the host PHP.
#
#   nix-shell
#   composer install
#   composer test
#
# CI still exercises the full PHP 8.0-8.5 matrix; this shell is only a local
# convenience, so the exact minor version here does not need to match.
{ pkgs ? import <nixpkgs> { } }:

pkgs.mkShell {
  packages = [
    pkgs.php82
    pkgs.php82.packages.composer
  ];
}
