<?php
namespace App\Filament\Resources\FaqResource\Pages;
use App\Filament\Resources\FaqResource;
use App\Models\FaqTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditFaq extends EditRecord {
    protected static string $resource = FaqResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function mutateFormDataBeforeFill(array $data): array {
        foreach (['en','tr'] as $l) {
            $t = $this->record->translations()->where('locale',$l)->first();
            if ($t) { $data["{$l}_question"]=$t->question; $data["{$l}_answer"]=$t->answer; }
        }
        return $data;
    }
    protected function afterSave(): void {
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
