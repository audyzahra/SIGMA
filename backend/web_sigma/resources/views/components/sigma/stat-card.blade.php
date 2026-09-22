@php
    $icon = $icon ?? 'default';
    $accent = $accent ?? 'blue';

    $icons = [
        'users' => '
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372
                    9.337 9.337 0 0 0 4.121-.952
                    4.125 4.125 0 0 0-7.533-2.493
                    M15 19.128v-.003
                    c0-1.113-.285-2.16-.786-3.07
                    M15 19.128v.003
                    A6.375 6.375 0 0 1 9.375 21
                    a6.375 6.375 0 0 1-5.625-3.375
                    c0-3.728 3.022-6.75 6.75-6.75
                    1.768 0 3.375.679 4.575 1.788
                    M12 6.375a3.375 3.375 0 1 1-6.75 0
                    3.375 3.375 0 0 1 6.75 0Z"/>
            </svg>
        ',

        'building' => '
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 21h16.5
                    M4.5 21V9.75
                    A1.5 1.5 0 0 1 6 8.25h12
                    a1.5 1.5 0 0 1 1.5 1.5V21
                    M8.25 8.25V5.625
                    A1.875 1.875 0 0 1 10.125 3.75h3.75
                    a1.875 1.875 0 0 1 1.875 1.875V8.25
                    M8.25 12h1.5
                    m-1.5 3h1.5
                    m4.5-3h1.5
                    m-1.5 3h1.5"/>
            </svg>
        ',

        'map' => '
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m9 18 6-3 6 3V6l-6-3-6 3-6-3v12l6 3Zm0 0V6
                    m6 9V3"/>
            </svg>
        ',

        'shield' => '
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75
                    m-3-7.036A11.959 11.959 0 0 1 3.598 6
                    11.99 11.99 0 0 0 3 9.749
                    c0 5.592 3.824 10.29 9 11.623
                    5.176-1.332 9-6.03 9-11.622
                    0-1.31-.21-2.573-.598-3.75
                    A11.959 11.959 0 0 1 12 2.714Z"/>
            </svg>
        ',

        'default' => '
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3v18m9-9H3"/>
            </svg>
        ',
    ];
@endphp

<article class="stat-card {{ $accent }}">

    <div class="stat-content">

        <p>{{ $label }}</p>

        <strong>{{ $value }}</strong>

        @if(isset($caption))
            <small>{!! $caption !!}</small>
        @endif

    </div>

    <span class="stat-icon">
        {!! $icons[$icon] ?? $icons['default'] !!}
    </span>

</article>