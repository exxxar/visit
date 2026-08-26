@props(['src' => null, 'default' => '/assets/placeholder.jpg', 'alt' => '', 'class' => ''])

<img
    src="{{ $src ?: $default }}"
    alt="{{ $alt }}"
    class="{{ $class }}"
    loading="lazy"
    onerror="this.onerror=null;this.src='{{ $default }}';this.classList.add('img-fallback')"
>
