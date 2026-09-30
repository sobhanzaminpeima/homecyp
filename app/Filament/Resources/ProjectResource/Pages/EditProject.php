<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['en', 'tr'] as $locale) {
            $t = $this->record->translations()->where('locale', $locale)->first();
            if ($t) {
                $data["{$locale}_title"] = $t->title;
                $data["{$locale}_description"] = $t->description;
                $data["{$locale}_short_description"] = $t->short_description;
                $data["{$locale}_meta_title"] = $t->meta_title;
                $data["{$locale}_meta_description"] = $t->meta_description;
            }
        }
        return $data;
    }

    protected function afterSave(): void
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
