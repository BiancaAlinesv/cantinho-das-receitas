@props(['variant' => 'primary', 'type' => 'button', 'as' => 'button', 'href' => null])

@php($classes = 'button '.match ($variant) {
    'outline' => 'button-outline',
    'danger' => 'button-danger',
    default => '',
})

@if($as === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
