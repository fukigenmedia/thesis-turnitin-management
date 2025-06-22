<x-mary-menu
    {{ $attributes }}
    activate-by-route
>
    <x-mary-menu-item
        title="Beranda"
        icon="m-rectangle-group"
        :link="route('dashboard')"
    />
    <x-mary-menu-item
        title="Berkas Turnitin"
        icon="s-bookmark-square"
        :link="route('users.index')"
    />
    <x-mary-menu-item
        title="Pengguna"
        icon="s-users"
        :link="route('users.index')"
        :hidden="!in_array(auth()->user()->role->value, ['admin'])"
    />
    <x-mary-menu-item
        title="Slider"
        icon="s-photo"
        :link="route('users.index')"
        :hidden="!in_array(auth()->user()->role->value, ['admin'])"
    />
    <li class="self-center">
        <x-mary-dropdown
            class="!p-0"
            no-x-anchor
            right
        >
            <x-slot:trigger>
                <x-mary-icon name="s-bell" />
                <x-mary-badge
                    class="badge-neutral badge-sm"
                    value="+99"
                />
            </x-slot:trigger>

            <div>
                Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos, laudantium?
            </div>
        </x-mary-dropdown>
    </li>
</x-mary-menu>
