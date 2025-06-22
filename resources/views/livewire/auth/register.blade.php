<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Enums\UserRole;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'mahasiswa';
    public string $password_confirmation = '';

    public array $roleOptions;

    public function mount(): void
    {
        $this->roleOptions = collect(UserRole::all())->reject(fn($role) => $role['id']->value === 'admin')->values()->all();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $passwordRules = app()->isProduction() ? ['required', 'string', 'confirmed', Rules\Password::defaults()] : ['required', 'string', 'confirmed'];

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role' => ['nullable', 'string', 'in:dosen,mahasiswa'],
            'password' => $passwordRules,
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $user->update([
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Create an account')"
        :description="__('Enter your details below to create your account')"
    />

    <!-- Session Status -->
    <x-auth-session-status
        class="text-center"
        :status="session('status')"
    />

    <form
        class="flex flex-col gap-6"
        wire:submit="register"
    >
        <x-mary-input
            type="text"
            :label="__('Name')"
            wire:model="name"
            :placeholder="__('Full name')"
            required
            autofocus
            autocomplete="name"
        />

        <x-mary-input
            type="email"
            :label="__('Email address')"
            wire:model="email"
            placeholder="email@example.com"
            required
            autocomplete="email"
        />

        <x-mary-group
            class="[&:checked]:!btn-primary"
            label="Jenis Akun"
            wire:model="role"
            :options="$roleOptions"
        />

        <x-mary-password
            wire:model="password"
            :placeholder="__('Password')"
            :label="__('Password')"
            required
            right
            autocomplete="new-password"
        />

        <x-mary-password
            wire:model="password_confirmation"
            :placeholder="__('Confirm password')"
            :label="__('Confirm password')"
            required
            right
            autocomplete="new-password"
        />

        <x-mary-button
            class="btn-accent"
            type="submit"
            :label="__('Create account')"
        />
    </form>

    <div class="text-base-content space-x-1 text-center text-sm rtl:space-x-reverse">
        {{ __('Already have an account?') }}
        <x-mary-button
            class="btn-link link-accent link-hover pl-0"
            :label="__('Log in')"
            :link="route('login')"
        />
    </div>
</div>
