{{-- Lucide icon from the sprite inlined at the top of the page (see content_lessons/create.blade.php). --}}
@props(['name'])

<svg {{ $attributes->class(['lu']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><use href="#{{ $name }}"></use></svg>
