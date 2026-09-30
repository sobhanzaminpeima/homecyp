<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\PropertyTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['en', 'tr'] as $locale) {
            $translation = $this->record->translations()->where('locale', $locale)->first();
            if ($translation) {
                $data["{$locale}_title"] = $translation->title;
                $data["{$locale}_description"] = $translation->description;
                $data["{$locale}_short_description"] = $translation->short_description;
                $data["{$locale}_meta_title"] = $translation->meta_title;
                $data["{$locale}_meta_description"] = $translation->meta_description;
            }
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        foreach (['en', 'tr'] as $locale) {
            if (!empty($data["{$locale}_title"])) {
                PropertyTranslation::updateOrCreate(
                    ['property_id' => $this->record->id, 'locale' => $locale],
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
