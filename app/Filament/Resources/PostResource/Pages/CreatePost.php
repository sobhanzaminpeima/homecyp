<?php
namespace App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource;
use App\Models\PostTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
class CreatePost extends CreateRecord {
    protected static string $resource = PostResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        if (empty($data['slug']) && !empty($data['en_title'])) $data['slug']=Str::slug($data['en_title']);
        $data['author_id']=auth()->id();
        return $data;
    }
    protected function afterCreate(): void { $this->saveTr(); }
    protected function saveTr(): void {
        $d=$this->form->getRawState();
        foreach (['en','tr'] as $l) {
            if (!empty($d["{$l}_title"])) {
                PostTranslation::updateOrCreate(['post_id'=>$this->record->id,'locale'=>$l],[
                    'title'=>$d["{$l}_title"]??'','excerpt'=>$d["{$l}_excerpt"]??null,'body'=>$d["{$l}_body"]??null,
                    'meta_title'=>$d["{$l}_meta_title"]??null,'meta_description'=>$d["{$l}_meta_description"]??null,
                ]);
            }
        }
    }
}
