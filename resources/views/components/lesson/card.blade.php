@props(['title', 'subtitle' => null, 'icon' => null])

<section {{ $attributes->class(['ls-card']) }}>
    <header class="ls-card-head">
        <div class="ls-card-title">
            @if ($icon)<x-lesson.icon :name="$icon" />@endif
            <div>
                <h2>{{ $title }}</h2>
                @if ($subtitle)<p>{{ $subtitle }}</p>@endif
            </div>
        </div>
        @isset($actions){{ $actions }}@endisset
    </header>
    <div class="ls-card-body">{{ $slot }}</div>
</section>
