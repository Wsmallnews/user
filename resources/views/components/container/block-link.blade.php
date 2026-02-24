@props([
    'href' => null,
])

<a
    {{ $attributes->merge(['class' => 'sn-user-container-block-link']) }}
    {{ \Filament\Support\generate_href_html($href) }}
>
    {{ $slot }}
</a>
