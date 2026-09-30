<?php
namespace App\Filament\Resources\AirbnbResource\Pages;
use App\Filament\Resources\AirbnbResource;
use App\Models\PropertyTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditAirbnb extends EditRecord {
    protected static string $resource = AirbnbResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function mutateFormDataBeforeFill(array $data): array {
        $t = $this->record->translations()->where('locale','en')->first();
        if ($t) { $data['en_title']=$t->title; $data['en_short_description']=$t->short_description; }
        return $data;
    }
    protected function afterSave(): void {
        $d = $this->form->getRawState();
        PropertyTranslation::updateOrCreate(
            ['property_id'=>$this->record->id,'locale'=>'en'],
            ['title'=>$d['en_title']??'','short_description'=>$d['en_short_description']??null]
        );
    }
}
