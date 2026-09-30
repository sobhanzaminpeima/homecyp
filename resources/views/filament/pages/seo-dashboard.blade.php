<x-filament-panels::page>
    @php $data = $this->getSeoData(); @endphp

    {{-- Global health --}}
    <x-filament::section heading="Global SEO Setup">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @php
                $g = $data['global'];
                $tiles = [
                    ['XML Sitemap', true, $g['sitemap']],
                    ['robots.txt', true, $g['robots']],
                    ['Google Analytics', $g['analytics'], null],
                    ['Meta Pixel', $g['pixel'], null],
                    ['reCAPTCHA', $g['recaptcha'], null],
                ];
            @endphp
            @foreach($tiles as [$label, $ok, $link])
            <div class="rounded-xl border p-4 text-center {{ $ok ? 'bg-green-50 border-green-200 dark:bg-green-950/30' : 'bg-amber-50 border-amber-200 dark:bg-amber-950/30' }}">
                <div class="text-2xl mb-1">{{ $ok ? '✅' : '⚠️' }}</div>
                <div class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $label }}</div>
                @if($link)<a href="{{ $link }}" target="_blank" class="text-[11px] text-primary-600 hover:underline">view</a>@endif
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-3">Configure Analytics, Pixel and reCAPTCHA keys in <a href="{{ route('filament.admin.pages.manage-settings') }}" class="text-primary-600 underline">Site Settings</a>.</p>
    </x-filament::section>

    {{-- Content audit --}}
    @foreach(['projects' => 'Projects', 'posts' => 'Blog Posts', 'properties' => 'Properties'] as $key => $heading)
    <x-filament::section :heading="$heading . ' — SEO Audit'" collapsible :collapsed="$key === 'properties'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b dark:border-gray-700">
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 px-2 text-center">Meta Title</th>
                        <th class="py-2 px-2 text-center">Meta Desc</th>
                        <th class="py-2 px-2 text-center">Image</th>
                        <th class="py-2 px-2 text-center">Content</th>
                        <th class="py-2 px-2 text-center">Score</th>
                        <th class="py-2 pl-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data[$key] as $row)
                    <tr class="border-b dark:border-gray-800">
                        <td class="py-2 pr-4 font-medium text-gray-800 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($row['title'], 45) }}</td>
                        <td class="py-2 px-2 text-center">{!! $row['checks']['meta_title'] ? '<span class=text-green-500>✓</span>' : '<span class=text-red-400 title="'.$row['metaTitleLen'].' chars (need 30-60)">✗</span>' !!}</td>
                        <td class="py-2 px-2 text-center">{!! $row['checks']['meta_description'] ? '<span class=text-green-500>✓</span>' : '<span class=text-red-400 title="'.$row['metaDescLen'].' chars (need 70-160)">✗</span>' !!}</td>
                        <td class="py-2 px-2 text-center">{!! $row['checks']['image'] ? '<span class=text-green-500>✓</span>' : '<span class=text-red-400>✗</span>' !!}</td>
                        <td class="py-2 px-2 text-center">{!! $row['checks']['content'] ? '<span class=text-green-500>✓</span>' : '<span class=text-red-400>✗</span>' !!}</td>
                        <td class="py-2 px-2 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $row['score'] >= 75 ? 'bg-green-100 text-green-700' : ($row['score'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $row['score'] }}%</span>
                        </td>
                        <td class="py-2 pl-2"><a href="{{ $row['editUrl'] }}" class="text-primary-600 hover:underline text-xs">Fix</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-4 text-center text-gray-400">No items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
    @endforeach
</x-filament-panels::page>
