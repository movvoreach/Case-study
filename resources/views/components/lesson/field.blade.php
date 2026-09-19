{{-- Label + control (slot) + help + inline validation message. `name` may use bracket syntax: document[document_file]. --}}
@props(['name' => null, 'label' => null, 'for' => null, 'required' => false, 'help' => null, 'optional' => false])

@php
    $key = $name ? \Illuminate\Support\Str::of($name)->replace(']', '')->replace('[', '.')->toString() : null;
    $error = $key ? $errors->first($key) : null;
@endphp

<div {{ $attributes->class(['ls-field', 'has-error' => (bool) $error]) }} @if ($key) data-field="{{ $key }}" @endif>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif>
            {{ $label }}
            @if ($required)<span class="ls-req" aria-hidden="true">*</span>@endif
            @if ($optional)<span class="ls-opt">Optional</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="ls-error" role="alert"><x-lesson.icon name="circle-alert" /> <span>{{ $error }}</span></p>
    @endif
    @if ($help)
        <p class="ls-help">{{ $help }}</p>
    @endif
</div>
