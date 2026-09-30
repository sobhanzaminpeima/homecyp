<?php
namespace App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource;
use App\Models\PageTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
class CreatePage extends CreateRecord {
    protected static string $resource = PageResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        if (empty($data['slug']) && !empty($data['en_title'])) $data['slug']=Str::slug($data['en_title']);
        return $data;
    }
    protected function afterCreate(): void { $this->saveTr(); }
    protected function saveTr(): void {
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
