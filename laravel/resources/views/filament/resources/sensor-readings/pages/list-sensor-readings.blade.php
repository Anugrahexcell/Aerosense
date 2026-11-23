<x-filament-panels::page>
    {{-- Auto-refresh removed: wire:poll caused blocking on single-threaded dev server --}}
    {{ $this->content }}
</x-filament-panels::page>
