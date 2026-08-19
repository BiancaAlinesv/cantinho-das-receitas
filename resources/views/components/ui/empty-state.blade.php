@props(['icon' => '♡', 'title', 'description'])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span aria-hidden="true">{{ $icon }}</span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    {{ $slot }}
</div>
