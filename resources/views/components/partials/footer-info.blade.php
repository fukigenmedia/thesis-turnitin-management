<footer {{ $attributes->merge(['class' => 'mt-auto text-center']) }}>
    <x-mary-menu-separator />

    <p class="text-base-content/70 text-xs">
        &copy; {{ date('Y') }} <span class="font-bold">{{ config('app.name', 'Laravel') }}</span>.
        Made with
        <x-mary-icon
            class="text-error inline h-4 w-4"
            name="o-heart"
        />
        by
        <a
            class="text-primary hover:underline"
            href="https://github.com/fukigenmedia"
            target="_blank"
            rel="noopener noreferrer"
        >Fukigen Media</a>.
    </p>
</footer>
