<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\PropertyTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateProperty extends CreateRecord
{
    protected static string $resource = PropertyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug']) && !empty($data['en_title'])) {
            $data['slug'] = Str::slug($data['en_title']);
        }

        // Agent submissions are auto-assigned to the agent and start as pending for admin approval.
        $user = Auth::user();
        if ($user && $user->hasRole('agent') && !$user->isAdmin()) {
            $data['agent_id'] = $user->id;
            $data['status'] = 'pending';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->saveTranslations($this->record->id);
    }

    private function saveTranslations(int $id): void
    {
        $data = $this->form->getRawState();
        foreach (['en', 'tr'] as $locale) {
            if (!empty($data["{$locale}_title"])) {
                PropertyTranslation::updateOrCreate(
                    ['property_id' => $id, 'locale' => $locale],
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
