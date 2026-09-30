<?php
namespace App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource;
use App\Models\PageTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPage extends EditRecord {
    protected static string $resource = PageResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function mutateFormDataBeforeFill(array $data): array {
        foreach (['en','tr'] as $l) {
            $t=$this->record->translations()->where('locale',$l)->first();
            if ($t) { $data["{$l}_title"]=$t->title; $data["{$l}_content"]=$t->content; $data["{$l}_meta_title"]=$t->meta_title; $data["{$l}_meta_description"]=$t->meta_description; }
        }
        return $data;
    }
    protected function afterSave(): void {
        $d=$this->form->getRawState();
        foreach (['en','tr'] as $l) {
            if (!empty($d["{$l}_title"])) {
                PageTranslation::updateOrCreate(['page_id'=>$this->record->id,'locale'=>$l],[
                    'title'=>$d["{$l}_title"]??'','content'=>$d["{$l}_content"]??null,
                    'meta_title'=>$d["{$l}_meta_title"]??null,'meta_description'=>$d["{$l}_meta_description"]??null,
                ]);
            }
        }
    }
}
