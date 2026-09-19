{{-- Pass `options` ([value => label]) for plain lists, or a slot for custom <option> markup (data attributes etc.). --}}
@props(['name', 'label' => null, 'value' => null, 'options' => null, 'required' => false, 'help' => null, 'wrapper' => null])

@php
    $key = \Illuminate\Support\Str::of($name)->replace(']', '')->replace('[', '.')->toString();
    $id = $attributes->get('id', 'f-' . str_replace('.', '-', $key));
    $invalid = $errors->has($key);
    $current = (string) old($key, $value);
@endphp

<x-lesson.field :name="$name" :label="$label" :for="$id" :required="$required" :help="$help" :class="$wrapper">
    <select name="{{ $name }}" id="{{ $id }}" aria-invalid="{{ $invalid ? 'true' : 'false' }}"
        {{ $attributes->except('id')->class(['ls-control', 'ls-select', 'is-invalid' => $invalid]) }}>
        @if ($options !== null)
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $optionValue === $current)>{{ $optionLabel }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</x-lesson.field>
