{{-- Drag & drop file area. `data-max-mb` on the input is enforced client-side; the server rules stay authoritative. --}}
@props([
    'name',
    'label' => null,
    'title' => 'Drag & Drop File',
    'hint' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'optional' => false,
    'icon' => 'cloud-upload',
    'kind' => 'single',
])

@php
    $key = \Illuminate\Support\Str::of($name)->replace(']', '')->replace('[', '.')->toString();
    $id = 'f-' . str_replace('.', '-', $key);
@endphp

<x-lesson.field :name="$name" :label="$label" :for="$id" :required="$required" :optional="$optional">
    <label class="ls-dropzone {{ $errors->has($key) ? 'is-invalid' : '' }}" data-dropzone="{{ $kind }}">
        <span class="ls-dz-icon"><x-lesson.icon :name="$icon" /></span>
        <strong>{{ $title }}</strong>
        <span class="ls-dz-or">or <u>Browse Files</u></span>
        @if ($hint)<span class="ls-dz-hint">{{ $hint }}</span>@endif
        <input type="file" class="ls-sr" id="{{ $id }}" name="{{ $name }}" @if ($accept) accept="{{ $accept }}" @endif
            @if ($multiple) multiple @endif {{ $attributes }}>
    </label>
    <ul class="ls-files" data-file-list="{{ $kind }}" aria-live="polite"></ul>
</x-lesson.field>
