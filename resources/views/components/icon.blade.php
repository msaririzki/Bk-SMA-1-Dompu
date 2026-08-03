@props(['name', 'class' => 'size-5'])
<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('dashboard')<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5zm10 0h6.5v4.5h-6.5zm0 8h6.5v8.5h-6.5zm-10 2h6.5v6.5h-6.5z"/>@break
        @case('students')<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.1a7.5 7.5 0 0 0-6 0M17.25 8.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM7.5 10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm14.25 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM9 20.25a6.75 6.75 0 0 0-7.5-5.17m13.5 5.17a6.75 6.75 0 0 1 7.5-5.17"/>@break
        @case('clipboard')<path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v12a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-12a2.25 2.25 0 0 0-2.25-2.25H15m-6 0A2.25 2.25 0 0 1 11.25 3h1.5A2.25 2.25 0 0 1 15 5.25m-6 0A2.25 2.25 0 0 0 11.25 7.5h1.5A2.25 2.25 0 0 0 15 5.25M8.25 12h7.5m-7.5 4.5h5.25"/>@break
        @case('documents')<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V6m-3.75-3.75V6h3.75m-9.75 6h4.5m-4.5 3h6m-6 3h6"/>@break
        @case('scale')<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0-16.5 6.75 3.75M12 4.5 5.25 8.25m13.5 0-3 5.25h6l-3-5.25Zm-13.5 0-3 5.25h6l-3-5.25ZM7.5 21h9"/>@break
        @case('upload')<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 8.25 12 3.75 7.5 8.25M12 3.75v12"/>@break
        @case('database')<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6c0 1.657-3.694 3-8.25 3S3.75 7.657 3.75 6 7.444 3 12 3s8.25 1.343 8.25 3Zm0 0v6c0 1.657-3.694 3-8.25 3s-8.25-1.343-8.25-3V6m16.5 6v6c0 1.657-3.694 3-8.25 3s-8.25-1.343-8.25-3v-6"/>@break
        @case('globe')<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.25-2.465 3.375-5.465 3.375-9S14.25 5.465 12 3m0 18c-2.25-2.465-3.375-5.465-3.375-9S9.75 5.465 12 3M3.6 9h16.8M3.6 15h16.8"/>@break
        @case('shield')<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75c2.1 1.8 4.575 2.7 7.425 2.7v5.1c0 4.275-2.475 7.425-7.425 9.45-4.95-2.025-7.425-5.175-7.425-9.45v-5.1c2.85 0 5.325-.9 7.425-2.7Zm-2.25 8.4 1.5 1.5 3.3-3.3"/>@break
        @case('menu')<path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('close')<path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>@break
        @case('logout')<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 8.25 18 12m0 0-3.75 3.75M18 12H7.5m3-8.25H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h4.5"/>@break
        @case('plus')<path stroke-linecap="round" d="M12 5v14M5 12h14"/>@break
        @case('arrow-right')<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>@break
        @case('arrow-left')<path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m5 5-5-5 5-5"/>@break
        @case('search')<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.1-5.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>@break
        @case('user')<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 21a7.5 7.5 0 0 1 15 0"/>@break
        @case('calendar')<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v3m10.5-3v3M3.75 9h16.5m-14.25-4.5h12A2.25 2.25 0 0 1 20.25 6.75v11.5A2.25 2.25 0 0 1 18 20.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6 4.5Z"/>@break
        @case('chart')<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5v-6h3v6h-3Zm6-10.5v10.5h3V9h-3Zm6-4.5v15h3v-15h-3Z"/>@break
        @case('check')<path stroke-linecap="round" stroke-linejoin="round" d="m5 12.5 4.25 4.25L19 7"/>@break
        @case('lock')<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5V7.25a4.5 4.5 0 0 1 9 0v3.25m-10.25 0h11.5A2.25 2.25 0 0 1 20 12.75v6A2.25 2.25 0 0 1 17.75 21H6.25A2.25 2.25 0 0 1 4 18.75v-6a2.25 2.25 0 0 1 2.25-2.25Z"/>@break
        @case('home')<path stroke-linecap="round" stroke-linejoin="round" d="m3 11.25 9-7.5 9 7.5M5.25 9.5v10.75h13.5V9.5M9 20.25v-6h6v6"/>@break
        @case('alert')<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 2.75 20.25h18.5L12 3.75Zm0 5.75v4.5m0 3.25h.01"/>@break
        @default<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
    @endswitch
</svg>
