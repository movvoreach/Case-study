{{-- Text-like input or textarea. Repopulates from old() and shows the server error underneath. --}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'optional' => false,
    'help' => null,
    'textarea' => false,
    'rows' => 3,
    'suffix' => null,
    'wrapper' => null,
])

@php
    $key = \Illuminate\Support\Str::of($name)->replace(']', '')->replace('[', '.')->toString();
    $id = $attributes->get('id', 'f-' . str_replace('.', '-', $key));
    $invalid = $errors->has($key);
    $current = old($key, $value);
@endphp

<x-lesson.field :name="$name" :label="$label" :for="$id" :required="$required" :optional="$optional" :help="$help"
    :class="$wrapper">
    @if ($textarea)
        <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" aria-invalid="{{ $invalid ? 'true' : 'false' }}"
            {{ $attributes->except('id')->class(['ls-control', 'is-invalid' => $invalid]) }}>{{ $current }}</textarea>
    @elseif ($suffix)
        <div class="ls-input-group">
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $current }}"
                aria-invalid="{{ $invalid ? 'true' : 'false' }}"
                {{ $attributes->except('id')->class(['ls-control', 'is-invalid' => $invalid]) }}>
            <span class="ls-suffix">{{ $suffix }}</span>
        </div>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $current }}"
            aria-invalid="{{ $invalid ? 'true' : 'false' }}"
            {{ $attributes->except('id')->class(['ls-control', 'is-invalid' => $invalid]) }}>
    @endif
</x-lesson.field>
