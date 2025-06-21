<x-mary-menu
    {{ $attributes }}
    activate-by-route
>
    <x-mary-menu-item
        title="Dashboard"
        icon="m-rectangle-group"
        :link="route('dashboard')"
    />
    <x-mary-menu-item
        title="Users"
        icon="s-users"
        :link="route('users.index')"
        :hidden="!in_array(auth()->user()->role, ['admin'])"
    />
</x-mary-menu>
