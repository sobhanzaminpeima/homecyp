<?php
namespace App\Filament\Resources\FaqResource\Pages;
use App\Filament\Resources\FaqResource;
use App\Models\FaqTranslation;
use Filament\Resources\Pages\CreateRecord;
class CreateFaq extends CreateRecord {
    protected static string $resource = FaqResource::class;
    protected function afterCreate(): void {
        $d = $this->form->getRawState();
        foreach (['en','tr'] as $l) {
            if (!empty($d["{$l}_question"])) {
                FaqTranslation::updateOrCreate(
                    ['faq_id'=>$this->record->id,'locale'=>$l],
                    ['question'=>$d["{$l}_question"]??'','answer'=>$d["{$l}_answer"]??'']
                );
            }
        }
    }
}
