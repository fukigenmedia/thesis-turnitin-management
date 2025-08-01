<?php

use Livewire\Volt\Component;

use App\Models\User;
use App\Models\TurnitinThread;

new class extends Component {
    public function with(): array
    {
        return [
            'count' => [
                'lecture' => User::where('role', 'dosen')->count(),
                'student' => User::where('role', 'mahasiswa')->count(),
                'files' => TurnitinThread::count(),
            ],
        ];
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="card bg-base-100 w-full shadow-sm">
                <div class="card-body">
                    <span class="badge badge-xs badge-soft">Total</span>
                    <div class="flex justify-between">
                        <h2 class="text-xl font-bold">Dosen</h2>
                        <span class="text-3xl font-bold">{{ $count['lecture'] }}</span>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 w-full shadow-sm">
                <div class="card-body">
                    <span class="badge badge-xs badge-soft">Total</span>
                    <div class="flex justify-between">
                        <h2 class="text-xl font-bold">Mahasiswa</h2>
                        <span class="text-3xl font-bold">{{ $count['student'] }}</span>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 w-full shadow-sm">
                <div class="card-body">
                    <span class="badge badge-xs badge-soft">Total</span>
                    <div class="flex justify-between">
                        <h2 class="text-xl font-bold">Berkas Turnitin</h2>
                        <span class="text-3xl font-bold">{{ $count['files'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-base-300 relative h-full flex-1 overflow-hidden rounded-xl border">
            <x-placeholder-pattern class="stroke-base-content/20 absolute inset-0 size-full" />
        </div>
    </div>
</div>
