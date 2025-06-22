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
        title="Pengguna"
        icon="s-users"
        :link="route('users.index')"
        :hidden="!in_array(auth()->user()->role->value, ['admin'])"
    />
</x-mary-menu>
