<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        <div class="mt-6">
            <x-filament::button type="submit" icon="heroicon-o-check">Save Changes</x-filament::button>
            <a href="{{ route('home') }}" target="_blank" class="ml-3 text-sm text-primary-600 hover:underline">Preview homepage →</a>
        </div>
    </form>
</x-filament-panels::page>
