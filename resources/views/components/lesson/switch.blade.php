{{-- Toggle row. After a failed submit the user's own choice wins over the default (unchecked boxes are absent from old()). --}}
@props(['name', 'label', 'desc' => null, 'checked' => false])

@php
    $id = 'sw-' . str_replace(['[', ']'], '-', $name);
    $isOn = session()->hasOldInput() ? (bool) old($name) : (bool) $checked;
@endphp

<div class="ls-switch">
    <label for="{{ $id }}" class="ls-switch-text">
        <span class="ls-switch-label">{{ $label }}</span>
        @if ($desc)<span class="ls-switch-desc">{{ $desc }}</span>@endif
    </label>
    <span class="ls-toggle">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" role="switch" @checked($isOn)>
        <span class="ls-toggle-track" aria-hidden="true"></span>
    </span>
</div>
