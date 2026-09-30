<x-filament-panels::page>
    <form wire:submit="import">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="import">Import Now</span>
                <span wire:loading wire:target="import">Importing… this may take up to a minute</span>
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8" heading="How importing works">
        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <li>Paste a <strong>Northernland</strong> project URL — the system fetches the page, downloads the cover and gallery images, detects amenities, and generates unique English SEO content (title, description, meta tags, FAQ, investment benefits).</li>
            <li>Imported items are saved as <strong>active</strong>. Review them in the Projects / Properties list, add Turkish content, set price &amp; featured status, then they appear on the website.</li>
            <li><strong>Hangiev</strong> and <strong>Airbnb</strong> block automated access. For those, create the listing manually in the Properties resource (toggle “Airbnb Listing” and paste the Airbnb URL to show the “Book on Airbnb” button).</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
