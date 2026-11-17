# Laravel Blade compatibility

This page tracks how much of [Laravel Blade](https://laravel.com/docs/master/blade) Katana supports. It doubles as the todo list.

## Legend

| Status | Meaning |
| ------ | ------- |
| ✅ | Supported and behaves as the Laravel docs describe |
| 🟨 | Partly supported; the notes list what is missing or different |
| ❌ | Not supported yet |
| 🔀 | Different on purpose; the notes explain the Katana way |
| 🚫 | Not planned |

## Displaying data

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Echo: `{{ }}` escaped, `{!! !!}` raw | ✅ | |
| Double encoding of HTML entities, `Blade::withoutDoubleEncoding()` | 🟨 | Katana never double-encodes (`&amp;` stays `&amp;`). Laravel double-encodes by default and offers the switch. Tracked in [#110](https://github.com/katanaphp/blade/issues/110). |
| Escaping Blade syntax: `@{{ }}`, `@{!! !!}`, `@@directive`, `@verbatim` | ✅ | |
| Comments: `{{-- --}}` | ✅ | |
| Rendering JSON: `Js::from()` | ❌ | Katana ships the older `@json` directive instead. Tracked in [#111](https://github.com/katanaphp/blade/issues/111). |

## Conditionals

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@if`, `@elseif`, `@else`, `@unless`, `@isset`, `@empty` | ✅ | |
| `@auth`, `@guest`, including the guard argument | 🔀 | Katana has no auth system. Register `Config::setAuthCallback()`; the guard is passed to the callback. Without a callback the directives throw. |
| `@production`, `@env` (name or list of names) | 🔀 | The environment comes from `Blade::setEnvironment()` and defaults to `production`. |
| `@hasSection`, `@sectionMissing` | ✅ | |
| `@session` | ❌ | Needs a session source. Tracked in [#48](https://github.com/katanaphp/blade/issues/48). |
| `@context` | ❌ | Needs a context source. Tracked in [#47](https://github.com/katanaphp/blade/issues/47). |
| `@switch`, `@case`, `@break`, `@default` | ✅ | |

## Loops

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@for`, `@foreach`, `@forelse` with `@empty`, `@while` | ✅ | |
| `@break`, `@continue`, plain and with a condition such as `@break($condition)` | ✅ | |
| `$loop`: `index`, `iteration`, `remaining`, `count`, `first`, `last`, `even`, `odd`, `depth`, `parent` | ✅ | |

## Classes, styles and form attributes

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@class`, `@style` | ✅ | |
| `@checked`, `@selected`, `@disabled`, `@readonly`, `@required` | ✅ | |

## Including subviews

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@include`, `@includeIf`, `@includeWhen`, `@includeUnless`, `@includeFirst`, `@includeIsolated` | ✅ | |
| `@each`, including the empty view and `$key` | ✅ | |

## `@once` and raw PHP

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@once` | ✅ | `@pushOnce` and `@prependOnce` are tracked under [Stacks](#stacks). |
| `@php` blocks and `<?php ?>` tags | ✅ | |
| `@use`: class, alias, grouped imports, `function` and `const` | ✅ | |

## Components

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Class-based components: component classes, constructor data, component methods, `shouldRender()`, `$except`, dependency injection, inline component views, `Blade::component()`, `Blade::componentNamespace()`, scoped slots via `$component->method()` | ❌ | Katana only has anonymous components. `$component` is set inside `<x-slot>` but has no user methods. Tracked in [#43](https://github.com/katanaphp/blade/issues/43). |
| Anonymous components: `<x-alert />`, nested `<x-forms.input />` | ✅ | |
| Anonymous index components: `card/card.blade.php`, `card/index.blade.php` | ✅ | |
| Anonymous component paths and namespaces: `<x-dashboard::panel />` | ✅ | `Blade::addAnonymousComponentPath($path, $namespace)`. |
| Passing data: plain attributes, `:attr="expression"`, kebab-case to camelCase, `{{ }}` inside attribute values | ✅ | |
| Short attribute syntax `:$name`, escaped attributes `::attr` | ✅ | |
| `@props`, including defaults | ✅ | |
| `@aware` | ❌ | Tracked in [#40](https://github.com/katanaphp/blade/issues/40). |
| Attribute bag output and merging: `{{ $attributes }}`, `merge()`, `class()`, `prepends()` | 🟨 | Values are wrapped in single quotes, Laravel uses double quotes. A boolean `true` renders as `disabled='1'`, Laravel renders `disabled="disabled"`. **Bug:** a boolean `false` renders as `disabled=''`, Laravel omits the attribute. `merge()` and `class()` put the default classes after the passed ones and append new attributes at the end, Laravel puts defaults first. `style` is not merged. `prepends()` joins without a separator, Laravel joins with a space. **Bug:** `prepends()` doubles the default when the attribute is not passed. Tracked in [#100](https://github.com/katanaphp/blade/issues/100). |
| Escaping of attribute values in `{{ $attributes }}` | 🟨 | Katana escapes every value and turns non-ASCII characters into entities (`ü` becomes `&uuml;`). Laravel escapes only bound values (`:title="$title"`) and leaves static values and non-ASCII characters as written. Tracked in [#112](https://github.com/katanaphp/blade/issues/112). |
| Attribute bag lookup and filtering: `has()`, `hasAny()`, `get()`, `filter()`, `whereStartsWith()`, `whereDoesntStartWith()`, `first()`, `only()`, `except()` | ✅ | |
| Slots: `{{ $slot }}`, named slots with `<x-slot name="title">`, `isEmpty()`, `hasActualContent()`, slot attributes | ✅ | |
| Short slot syntax: `<x-slot:title>` | ❌ | **Bug:** currently fails with a `TypeError` instead of rendering. This is the syntax the Laravel docs use. With it, Laravel treats an extra `name="…"` as a normal attribute of the slot. Tracked in [#42](https://github.com/katanaphp/blade/issues/42). |
| Dynamic components: `<x-dynamic-component :component="$name" />` | ❌ | Tracked in [#41](https://github.com/katanaphp/blade/issues/41). |

## Layouts

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Layouts using components | ✅ | Use `<x-slot name="title">` until the short slot syntax works. |
| Template inheritance: `@extends`, `@section` (block and inline), `@endsection`, `@show`, `@yield` with default, `@parent` | ✅ | |

## Forms

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@csrf` | 🔀 | Katana has no session. Register `Config::setCsrfCallback()` to return the field. |
| `@method` | ❌ | Tracked in [#113](https://github.com/katanaphp/blade/issues/113). |
| `@error`, including `$message` and named error bags | ❌ | Needs an error source. Tracked in [#37](https://github.com/katanaphp/blade/issues/37). |

## Stacks

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@push`, `@stack`, `@hasstack` | ✅ | |
| `@prepend`, `@pushIf`, `@pushOnce`, `@prependOnce` | ❌ | Tracked in [#36](https://github.com/katanaphp/blade/issues/36) (`@pushIf`) and [#45](https://github.com/katanaphp/blade/issues/45) (`@pushOnce`). |

## Service injection

| Feature | Status | Notes |
| ------- | ------ | ----- |
| `@inject` | ❌ | A framework decision: Katana has no container, so this needs a resolver callback or a framework integration. Tracked in [#114](https://github.com/katanaphp/blade/issues/114). |

## Rendering

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Rendering a view: `view('name', $data)` | ✅ | `$blade->render('name', $data)`. |
| Inline templates: `Blade::render($string, $data)` | ❌ | Tracked in [#34](https://github.com/katanaphp/blade/issues/34). |
| Fragments: `@fragment`, `fragment()`, `fragments()`, `fragmentIf()`, `fragmentsIf()` | ✅ | A fragment name that does not exist renders as an empty string; Laravel returns the whole view. Tracked in [#105](https://github.com/katanaphp/blade/issues/105). |

## Extending Blade

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Custom directives: `Blade::directive()` | ✅ | `$blade->directive()`. |
| Custom echo handlers: `Blade::stringable()` | ✅ | `$blade->stringable()`. |
| Custom if statements: `Blade::if()` | 🟨 | `Config::if()` gives `@name`, `@else` and `@endname`. `@elsename` causes a parse error and `@unlessname` is not compiled. Tracked in [#31](https://github.com/katanaphp/blade/issues/31). |

## Laravel tooling

| Feature | Status | Notes |
| ------- | ------ | ----- |
| Artisan commands (`make:component`, `view:clear`), Livewire | 🚫 | Framework tooling, outside the scope of a standalone renderer. |

## Known bugs

These are not tied to a single feature above:

- Email-like text such as `support@php.net` is compiled as a directive and fails. Tracked in [#107](https://github.com/katanaphp/blade/issues/107).
- Stacks, sections and `@once` keep their state between renders on the same `Blade` instance. Tracked in [#108](https://github.com/katanaphp/blade/issues/108).
- Whitespace after a directive without parentheses is removed from the output. Tracked in [#109](https://github.com/katanaphp/blade/issues/109).

## Katana extras

These are supported by Katana but are not part of the current Laravel Blade docs:

- `@json`, the older Laravel directive for printing JSON.
- `@component` and `@slot`, the directive syntax for components, kept for projects ported from BladeOne or older Laravel versions.
- Custom view sources by extending the `ViewFinder` class.
