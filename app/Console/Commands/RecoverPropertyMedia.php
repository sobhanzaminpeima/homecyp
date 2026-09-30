<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;

class RecoverPropertyMedia extends Command
{
    protected $signature = 'media:recover-property-images {--gallery=4 : Number of gallery images to attach to each item}';

    protected $description = 'Recover property and project images from orphaned files on the public media disk';

    public function handle(): int
    {
        // Shared hosting commonly disables proc_open. Optimizers use external
        // processes, while the media conversions themselves work through GD.
        config(['media-library.image_optimizers' => []]);

        $sources = $this->sourceImages();

        if ($sources === []) {
            $this->error('No source images were found in storage/app/public.');

            return self::FAILURE;
        }

        $this->moveMediaSequencePastExistingDirectories();

        $galleryCount = max(0, min(12, (int) $this->option('gallery')));
        $recovered = 0;

        Project::query()->with('media')->orderBy('id')->each(function (Project $project) use ($sources, $galleryCount, &$recovered): void {
            $recovered += $this->recoverFor($project, $sources, $galleryCount);
        });

        Property::query()->with('media')->where('status', 'active')->orderBy('id')->each(function (Property $property) use ($sources, $galleryCount, &$recovered): void {
            $recovered += $this->recoverFor($property, $sources, $galleryCount);
        });

        $this->info("Recovered {$recovered} media records from ".count($sources).' available source images.');

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function sourceImages(): array
    {
        $disk = Storage::disk('public');

        return collect($disk->allFiles())
            ->filter(fn (string $path): bool => preg_match('/\.(?:jpe?g|png|webp)$/i', $path) === 1)
            ->reject(fn (string $path): bool => str_contains($path, '/conversions/') || str_starts_with($path, 'livewire-tmp/'))
            ->filter(fn (string $path): bool => is_file($disk->path($path)))
            ->values()
            ->all();
    }

    private function recoverFor(Model&HasMedia $model, array $sources, int $galleryCount): int
    {
        $hasCover = $model->getMedia('cover')->isNotEmpty();
        $hasGallery = $model->getMedia('gallery')->isNotEmpty();

        if ($hasCover && ($hasGallery || $galleryCount === 0)) {
            return 0;
        }

        $offset = abs(crc32($model::class.'#'.$model->getKey())) % count($sources);
        $added = 0;

        try {
            if (! $hasCover) {
                $model->addMedia(Storage::disk('public')->path($sources[$offset]))
                    ->preservingOriginal()
                    ->toMediaCollection('cover', 'public');
                $added++;
            }

            if (! $hasGallery && $galleryCount > 0) {
                for ($index = 1; $index <= $galleryCount; $index++) {
                    $source = $sources[($offset + $index) % count($sources)];
                    $model->addMedia(Storage::disk('public')->path($source))
                        ->preservingOriginal()
                        ->toMediaCollection('gallery', 'public');
                    $added++;
                }
            }
        } catch (\Throwable $exception) {
            $this->warn(sprintf('%s #%s: %s', $model::class, $model->getKey(), $exception->getMessage()));
        }

        return $added;
    }

    private function moveMediaSequencePastExistingDirectories(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $largestDirectory = collect(Storage::disk('public')->directories())
            ->filter(fn (string $directory): bool => ctype_digit(basename($directory)))
            ->map(fn (string $directory): int => (int) basename($directory))
            ->max() ?? 0;

        if ($largestDirectory > 0) {
            DB::statement('ALTER TABLE media AUTO_INCREMENT = '.($largestDirectory + 1));
        }
    }
}
