<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="bg-base-200 min-h-screen font-sans antialiased">
        <x-mary-nav
            sticky
            full-width
        >
            {{-- BRAND --}}
            <x-slot:brand>
                <a
                    class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
                    href="/"
                    wire:navigate
                >
                    <x-app-logo />
                </a>
            </x-slot:brand>

            {{-- MENU AND ACTIONS --}}
            <x-slot:actions>
                {{-- MENU --}}
                <div class="hidden items-center justify-end gap-4 lg:flex rtl:space-x-reverse">
                    <x-partials.menu class="menu-horizontal space-x-2 !p-0" />
                    <livewire:settings.user-menu />
                </div>

                {{-- MOBILE TRIGGER BUTTON --}}
                <label
                    class="lg:hidden"
                    for="main-drawer"
                >
                    <x-mary-icon
                        class="cursor-pointer"
                        name="o-bars-3"
                    />
                </label>
            </x-slot:actions>
        </x-mary-nav>

        <x-mary-main full-width>
            {{-- SIDEBAR MOBILE ONLY --}}
            <x-slot:sidebar
                class="bg-base-100 lg:hidden"
                drawer="main-drawer"
                collapsible
            >

                {{-- BRAND --}}
                <div class="m-3 flex items-center justify-between">
                    <a
                        class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
                        href="{{ route('dashboard') }}"
                        wire:navigate
                    >
                        <x-app-logo />
                    </a>
                </div>

                {{-- USER MENU --}}
                <div class="mx-3">
                    <x-mary-menu-separator />
                    <livewire:settings.user-menu />
                    <x-mary-menu-separator />
                </div>

                {{-- MENU --}}
                <x-partials.menu />
            </x-slot:sidebar>

            {{-- The `$slot` goes here --}}
            <x-slot:content
                class="container flex min-h-screen flex-col"
            >
                <div class="flex flex-1 flex-col items-stretch gap-2">
                    @if (session('alert'))
                        <x-mary-alert
                            class="alert-{{ session('alert')['type'] ?? 'info' }}"
                            :title="session('alert')['title'] ?? 'Info'"
                            :description="session('alert')['description'] ?? 'This is an alert message.'"
                            dismissible
                        ></x-mary-alert>
                    @endif

                    {{ $slot }}
                </div>
                <x-partials.footer-info />
            </x-slot:content>
        </x-mary-main>

        <x-mary-toast />
    </body>

</html>
