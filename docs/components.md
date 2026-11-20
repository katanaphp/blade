# Components

Components provide a way to create reusable elements that can be used in multiple places in your application, in Katana they look and act like html tags. Currently, Katana only supports anynomous components which are simple blade template files, but in the future, Katana will support class components which will be more powerful and flexible.

## Creating a component

To create a component, you need to create a blade template file in the inside `components` directory of your `views` directory. The file name should be the name of the component with the `.blade.php` extension. For example, if you want to create a component called `button`, you should create a file called `button.blade.php`.

Here is an example of a simple component:

```blade
{{-- components/button.blade.php --}}
<button> Simple </button>

{{-- Using button --}}
<x-button></x-button> or <x-button/>
```

## Passing data to components

On its own, a component is just a static element, but you can pass data to a component using attributes. Here is an example of a component that accepts a `name` attribute:

```blade
{{-- components/alert.blade.php --}}

<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>

{{-- Using alert --}}

<x-alert type="info" message="Hello world"/>
```

### Passing Variables

You can pass variables to components using the `:variable` syntax. Here is an example:

```blade
{{-- components/alert.blade.php --}}

<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>

{{-- Using alert --}}
@php
    $message = "Hello world";
    $type = "info";
@endphp

<x-alert :type="$type" :message="$message"/>
```

> [!Info]
> Variables with multiple words should be separated by a dash `-` in the component tag. For example, `alert-type` instead of `alertType`.

> [!Info]
> Multiple word attributes are converted to camelCase in the component file. For example, `alert-type` will be accessible using `$alertType` in the component file.

### Default values

You can set default values for attributes in a component using the `@props` directive. Here is an example:

```blade
{{-- components/alert.blade.php --}}
@props(['alertType' => 'info', 'message'])


<div class="alert alert-{{ $alertType }}">
    {{ $message }}
</div>

{{-- Using alert --}}
<x-alert message="Hello world"/>
```

### Component Attributes

You can access all the attributes passed to a component using the `$attributes` variable. Here is an example:

```blade
{{-- components/alert.blade.php --}}

<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
    {{ $message }}
</div>

{{-- Using alert --}}
<x-alert type="info" message="Hello world" class="mt-4"/>
``
```

#### Attributes methods

- `merge(array $defaultAttributes)`: Merges the given array with the component's attributes.
- `except(array $keys)`: Remove the given keys from the component's attributes.
- `only(array $keys)`: Keep only the given keys from the component's attributes.
- `filter(callable $callback)`: Keep the attributes for which the callback returns true. The callback receives the value first, then the key.
- `first(string $key)`: Get the first value from the component's attributes for the given key.
- `get(string $key)`: Get the value from the component's attributes for the given key.
- `has(string $key)`: Determine if the component's attributes contain a value for the given key.
- `hasAny(array $keys)`: Determine if the component's attributes contain a value for any of the given keys.
