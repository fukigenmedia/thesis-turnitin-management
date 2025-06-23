<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="bg-base-200 min-h-screen font-sans antialiased">
        <x-mary-nav
            class="z-50"
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
                    @if (Route::has('login'))
                        <nav class="flex items-center justify-end gap-1">
                            @auth
                                <a
                                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                    href="{{ url('/dashboard') }}"
                                >
                                    Beranda
                                </a>
                            @else
                                <a
                                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                    href="{{ route('login') }}"
                                >
                                    Masuk
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                        href="{{ route('register') }}"
                                    >
                                        Daftar
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
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

                    {{--  --}}

                    <x-mary-menu-separator />
                </div>

                {{-- MENU --}}
            </x-slot:sidebar>

            <x-slot:content
                class="container flex min-h-screen flex-col"
            >
                <div class="flex flex-1 flex-col items-stretch gap-2">
                    <livewire:welcome />
                </div>

                <x-partials.footer-info />
            </x-slot:content>
        </x-mary-main>
    </body>

</html>
