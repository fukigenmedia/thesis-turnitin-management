<?php

use Livewire\Volt\Component;

use App\Models\Slider;

new class extends Component {
    public function with(): array
    {
        return [
            'sliders' => Slider::where('status', true)->take(5)->get(),
        ];
    }
}; ?>

<div>
    <div
        class="carousel relative w-full rounded"
        x-data="{
            active: 0,
            total: {{ count($sliders) }},
            interval: null,
            start() {
                this.interval = setInterval(() => {
                    this.next();
                }, 5000);
            },
            stop() {
                clearInterval(this.interval);
            },
            next() {
                this.active = (this.active + 1) % this.total;
            },
            prev() {
                this.active = (this.active - 1 + this.total) % this.total;
            }
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
    >
        @foreach ($sliders as $index => $slider)
            <div
                class="carousel-item absolute w-full transition-opacity duration-700"
                id="slide{{ $index + 1 }}"
                :class="{ 'opacity-100 z-10 relative': active === {{ $index }}, 'opacity-0 z-0': active !==
                        {{ $index }} }"
                x-show="active === {{ $index }}"
                x-transition.opacity
            >
                <img
                    class="h-96 w-full object-cover"
                    src="{{ $slider->photo ?? 'https://via.placeholder.com/800x400?text=No+Image' }}"
                    alt="{{ $slider->name ?? 'Slider Image' }}"
                />
                <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
                    <button
                        class="btn btn-circle"
                        type="button"
                        @click="prev"
                    >❮</button>
                    <button
                        class="btn btn-circle"
                        type="button"
                        @click="next"
                    >❯</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
