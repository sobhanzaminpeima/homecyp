<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug']) && !empty($data['en_title'])) {
            $data['slug'] = Str::slug($data['en_title']);
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        foreach (['en', 'tr'] as $locale) {
            if (!empty($data["{$locale}_title"])) {
                ProjectTranslation::updateOrCreate(
                    ['project_id' => $this->record->id, 'locale' => $locale],
                    [
                        'title' => $data["{$locale}_title"] ?? '',
                        'description' => $data["{$locale}_description"] ?? null,
                        'short_description' => $data["{$locale}_short_description"] ?? null,
                        'meta_title' => $data["{$locale}_meta_title"] ?? null,
                        'meta_description' => $data["{$locale}_meta_description"] ?? null,
                    ]
                );
            }
        }
    }
}
